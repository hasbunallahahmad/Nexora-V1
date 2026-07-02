<?php

namespace App\Filament\Exports;

use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class AgendaExport extends ExcelExport implements WithColumnWidths, WithEvents
{
    public static ?string $selectedBulan = null;

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalDataRows = $sheet->getHighestRow();
                $sheet->insertNewRowBefore(1, 4);
                $headerRow = 5;
                $lastRow   = $sheet->getHighestRow();

                // Baris 1: Nama instansi
                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', 'Agenda Kegiatan Dinas Arsip dan Perpustakaan Kota Semarang');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'name' => 'Calibri'],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(25);

                // Baris 2: Tahun
                $sheet->mergeCells('A2:F2');
                $sheet->setCellValue('A2', 'Tahun ' . now()->year);
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'name' => 'Calibri'],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(20);

                // Baris 3: Bulan — ambil langsung dari request
                $namaBulan = static::$selectedBulan
                    ? \Carbon\Carbon::create()->month((int) static::$selectedBulan)->translatedFormat('F')
                    : 'Semua Bulan';

                $sheet->mergeCells('A3:F3');
                $sheet->setCellValue('A3', 'Bulan : ' . $namaBulan);
                $sheet->getStyle('A3')->applyFromArray([
                    'font'      => ['size' => 11, 'name' => 'Calibri'],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(20);

                // Baris 4: Spacer
                $sheet->getRowDimension(4)->setRowHeight(8);

                $sheet->getStyle("A{$headerRow}:F{$headerRow}")->applyFromArray([
                    'font' => [
                        'bold'  => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                        'size'  => 11,
                        'name'  => 'Calibri',
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF1E3A5F'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                ]);
                $sheet->getRowDimension($headerRow)->setRowHeight(30);

                for ($row = $headerRow + 1; $row <= $lastRow; $row++) {
                    $bgColor = ($row % 2 === 0) ? 'FFF0F4FA' : 'FFFFFFFF';
                    $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                        'fill' => [
                            'fillType'   => Fill::FILL_SOLID,
                            'startColor' => ['argb' => $bgColor],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_TOP,
                            'wrapText' => true,
                        ],
                        'font' => ['size' => 10, 'name' => 'Calibri'],
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(40);
                }

                $sheet->getStyle("A{$headerRow}:F{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFCCCCCC'],
                        ],
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color'       => ['argb' => 'FF1E3A5F'],
                        ],
                    ],
                ]);
            },
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40,
            'B' => 40,
            'C' => 30,
            'D' => 20,
            'E' => 20,
            'F' => 35,
        ];
    }
}
