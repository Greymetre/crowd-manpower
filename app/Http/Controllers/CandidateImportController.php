<?php

namespace App\Http\Controllers;

use App\Http\Requests\CandidateRequest;
use App\Models\Candidate;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class CandidateImportController extends Controller
{
    /** Extra header spellings accepted on import (normalised: lowercase, letters/digits only). */
    private const ALIASES = [
        'idno' => 'id_no', 'candidateid' => 'id_no', 'pin' => 'pincode', 'pincode' => 'pincode',
        'dateofbirth' => 'dob', 'maritalstatus' => 'marital_status', 'marital' => 'marital_status',
        'sex' => 'gender', 'qualification' => 'education', 'applieddate' => 'applied_date',
        'dateofapplication' => 'applied_date', 'applydate' => 'applied_date',
    ];

    private const MAX_ERRORS_SHOWN = 50;

    public function create(): View
    {
        return view('candidates.import', ['columns' => $this->importColumns()]);
    }

    public function template(): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'tpl').'.xlsx';

        $writer = new Writer();
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(array_values($this->importColumns()), (new Style())->setFontBold()));
        $writer->addRow(Row::fromValues([
            'Ramesh Kumar', 'CMS-1001', 'Yes', 'Ward 4, Main Road', 'Chomu', 'Chomu', 'Jaipur', 'Rajasthan',
            '303702', '15-08-1995', '', 'Married', 'Male', '12th', now()->format('d-m-Y'),
        ]));
        $writer->close();

        return response()->download($path, 'candidate-import-template.xlsx')->deleteFileAfterSend();
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:xlsx,csv,txt'],
        ], [], ['file' => 'file']);

        $file = $request->file('file');
        $reader = strtolower($file->getClientOriginalExtension()) === 'xlsx' ? new XlsxReader() : new CsvReader();

        try {
            $reader->open($file->getRealPath());
            [$rows, $errors] = $this->parse($reader);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['file' => 'Could not read this file. Please upload a valid .xlsx or .csv file.']);
        } finally {
            $reader->close();
        }

        if ($rows === null) {
            return back()->withErrors(['file' => 'No "Name" column found in the first row. Please use the template headings.']);
        }

        $userId = $request->user()->id;
        $now = now();

        DB::transaction(function () use ($rows, $userId, $now) {
            foreach (array_chunk($rows, 500) as $chunk) {
                Candidate::insert(array_map(
                    fn (array $row) => $row + ['created_by' => $userId, 'created_at' => $now, 'updated_at' => $now],
                    $chunk,
                ));
            }
        });

        return redirect()->route('candidates.import')
            ->with('status', count($rows).' '.(count($rows) === 1 ? 'record' : 'records').' imported successfully.')
            ->with('import_errors', array_slice($errors, 0, self::MAX_ERRORS_SHOWN))
            ->with('import_error_total', count($errors));
    }

    /**
     * @return array{0: ?array<int, array>, 1: array<int, string>} valid rows (null if header invalid) and error messages
     */
    private function parse(XlsxReader|CsvReader $reader): array
    {
        $map = null;
        $rows = [];
        $errors = [];
        $fields = array_keys(CandidateRequest::fieldRules());

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $line => $row) {
                $cells = $row->toArray();

                if ($map === null) {
                    $map = $this->headerMap($cells);
                    if (! in_array('name', $map, true)) {
                        return [null, []];
                    }

                    continue;
                }

                $data = array_fill_keys($fields, null);
                foreach ($map as $index => $key) {
                    $data[$key] = $this->clean($key, $cells[$index] ?? null);
                }

                if (count(array_filter($data, fn ($v) => $v !== null)) === 0) {
                    continue; // blank line
                }

                $validator = Validator::make($data, CandidateRequest::fieldRules());
                if ($validator->fails()) {
                    $errors[] = 'Row '.$line.': '.implode(' ', $validator->errors()->all());

                    continue;
                }

                if ($data['dob']) {
                    $data['age'] = Candidate::ageFromDob($data['dob']);
                }
                $rows[] = $data;
            }

            break; // first sheet only
        }

        return [$map === null ? null : $rows, $errors];
    }

    /** Column index => field key, based on the header row. */
    private function headerMap(array $cells): array
    {
        $known = [];
        foreach ($this->importColumns() as $key => $label) {
            $known[$this->normalise($label)] = $key;
            $known[$this->normalise($key)] = $key;
        }
        $known = self::ALIASES + $known;

        $map = [];
        foreach ($cells as $index => $heading) {
            $key = $known[$this->normalise((string) $heading)] ?? null;
            if ($key && ! in_array($key, $map, true)) {
                $map[$index] = $key;
            }
        }

        return $map;
    }

    private function clean(string $key, mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return in_array($key, ['dob', 'applied_date'], true) ? $value->format('Y-m-d') : $value->format('d-m-Y');
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return match ($key) {
            'dob', 'applied_date' => $this->parseDate($value),
            'pincode' => preg_replace('/\.0+$/', '', $value),
            'age' => is_numeric($value) ? (int) $value : $value,
            'gender' => match (strtolower($value)) {
                'm', 'male' => 'Male',
                'f', 'female' => 'Female',
                'o', 'other' => 'Other',
                default => $value,
            },
            'marital_status' => ucfirst(strtolower($value)),
            default => $value,
        };
    }

    /** Accepts d-m-Y, d/m/Y, d.m.Y and Y-m-d. Unparseable values are left for validation to reject. */
    private function parseDate(string $value): string
    {
        foreach (['d-m-Y', 'd/m/Y', 'd.m.Y', 'Y-m-d', 'd-m-y', 'd/m/y'] as $format) {
            $date = DateTimeImmutable::createFromFormat('!'.$format, $value);
            $problems = DateTimeImmutable::getLastErrors();
            if ($date && ! ($problems && ($problems['warning_count'] || $problems['error_count']))) {
                return $date->format('Y-m-d');
            }
        }

        return $value;
    }

    private function normalise(string $text): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower($text));
    }

    /** Import columns = all form columns except the auto-generated Sr No. */
    private function importColumns(): array
    {
        return array_diff_key(Candidate::COLUMNS, ['id' => true]);
    }
}
