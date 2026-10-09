<?php

namespace App\Http\Controllers;

use App\Exports\CandidatePdf;
use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CandidateExportController extends Controller
{
    public function excel(Request $request): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'candidates').'.xlsx';

        $writer = new Writer();
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(array_values(Candidate::COLUMNS), (new Style())->setFontBold()));

        foreach ($this->query($request)->lazyById(500) as $candidate) {
            $writer->addRow(Row::fromValues(array_map(
                fn (string $key) => $key === 'id' || $key === 'age' ? $candidate->{$key} : $candidate->display($key),
                array_keys(Candidate::COLUMNS),
            )));
        }

        $writer->close();

        return response()->download($path, $this->filename('xlsx'))->deleteFileAfterSend();
    }

    public function pdf(Request $request): Response
    {
        $query = $this->query($request);
        $total = (clone $query)->count();

        $pdf = new CandidatePdf(
            config('app.company'),
            sprintf('Candidate records  |  %d %s  |  Generated %s', $total, $total === 1 ? 'record' : 'records', now()->format('d-m-Y h:i A')),
        );
        $pdf->addRows($query->lazyById(500));

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->filename('pdf').'"',
        ]);
    }

    private function query(Request $request)
    {
        return Candidate::query()->filter($request->only(CandidateController::FILTERS))->orderBy('id');
    }

    private function filename(string $ext): string
    {
        return 'candidates-'.now()->format('Y-m-d-His').'.'.$ext;
    }
}
