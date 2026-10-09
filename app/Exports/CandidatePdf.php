<?php

namespace App\Exports;

use App\Models\Candidate;
use FPDF;

/**
 * Landscape A4 table of candidates, built on FPDF (tiny, no extra deps).
 * FPDF only supports Latin-1, so non-Latin text (e.g. Hindi) is transliterated.
 */
class CandidatePdf extends FPDF
{
    /** Column widths in mm, matching Candidate::COLUMNS order (sum = 281). */
    private const WIDTHS = [
        'id' => 10, 'name' => 29, 'id_no' => 18, 'working' => 17, 'address' => 35, 'village' => 18,
        'tehsil' => 16, 'district' => 17, 'state' => 16, 'pincode' => 13, 'dob' => 16, 'age' => 8,
        'marital_status' => 21, 'gender' => 12, 'education' => 16, 'applied_date' => 19,
    ];

    public function __construct(private string $title, private string $subtitle)
    {
        parent::__construct('L', 'mm', 'A4');
        $this->SetMargins(8, 8, 8);
        $this->SetAutoPageBreak(true, 12);
        $this->AliasNbPages();
        $this->SetTitle($this->latin($title));
    }

    public function Header(): void
    {
        $this->SetFont('Helvetica', 'B', 13);
        $this->SetTextColor(15, 23, 42);
        $this->Cell(0, 7, $this->latin($this->title), 0, 1);
        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(0, 5, $this->latin($this->subtitle), 0, 1);
        $this->Ln(2);

        $this->SetFont('Helvetica', 'B', 7);
        $this->SetFillColor(79, 70, 229);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(226, 232, 240);
        foreach (Candidate::COLUMNS as $key => $label) {
            $this->Cell(self::WIDTHS[$key], 7, $this->fit($label, self::WIDTHS[$key]), 1, 0, 'L', true);
        }
        $this->Ln();
    }

    public function Footer(): void
    {
        $this->SetY(-10);
        $this->SetFont('Helvetica', '', 7);
        $this->SetTextColor(148, 163, 184);
        $this->Cell(0, 5, 'Page '.$this->PageNo().' of {nb}', 0, 0, 'R');
    }

    public function addRows(iterable $candidates): void
    {
        $this->AddPage();
        $this->SetFont('Helvetica', '', 7);
        $this->SetTextColor(30, 41, 59);

        $count = 0;
        foreach ($candidates as $candidate) {
            $fill = $count++ % 2 === 1;
            $this->SetFillColor(248, 250, 252);
            foreach (self::WIDTHS as $key => $width) {
                $this->Cell($width, 6, $this->fit($candidate->display($key), $width), 1, 0, 'L', $fill);
            }
            $this->Ln();
        }

        if ($count === 0) {
            $this->Cell(array_sum(self::WIDTHS), 10, 'No records found.', 1, 1, 'C');
        }
    }

    /** Truncate text with "..." so it never overflows its cell. */
    private function fit(string $text, float $width): string
    {
        $text = $this->latin($text);
        $max = $width - 2;
        if ($this->GetStringWidth($text) <= $max) {
            return $text;
        }
        while ($text !== '' && $this->GetStringWidth($text.'...') > $max) {
            $text = mb_substr($text, 0, -1);
        }

        return $text.'...';
    }

    private function latin(string $text): string
    {
        return (string) iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text);
    }
}
