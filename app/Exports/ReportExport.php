<?php

namespace App\Exports;

use App\Models\Sale;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    WithColumnWidths
{
    protected string $reportType;
    protected string $period;
    protected string $selectedDate;
    protected string $selectedMonth;
    protected int $selectedYear;

    public function __construct(
        string $reportType = 'sales',
        string $period = 'monthly',
        string $selectedDate = '',
        string $selectedMonth = '',
        int $selectedYear = 0
    ) {
        $this->reportType = $reportType;
        $this->period = $period;

        $this->selectedDate = $selectedDate
            ?: Carbon::today()->format('Y-m-d');

        $this->selectedMonth = $selectedMonth
            ?: Carbon::today()->format('Y-m');

        $this->selectedYear = $selectedYear
            ?: Carbon::today()->year;
    }

    /**
     * Mengambil data laporan.
     */
    public function collection(): Collection
    {
        if ($this->reportType === 'sales') {

            $query = Sale::query()
                ->with([
                    'saleDetails.product'
                ]);

            /*
            |--------------------------------------------------------------------------
            | FILTER PENJUALAN
            |--------------------------------------------------------------------------
            */

            if ($this->period === 'daily') {

                $query->whereDate(
                    'sale_date',
                    $this->selectedDate
                );

            } elseif ($this->period === 'monthly') {

                $monthDate = Carbon::createFromFormat(
                    'Y-m',
                    $this->selectedMonth
                );

                $query
                    ->whereYear(
                        'sale_date',
                        $monthDate->year
                    )
                    ->whereMonth(
                        'sale_date',
                        $monthDate->month
                    );

            } else {

                $query->whereYear(
                    'sale_date',
                    $this->selectedYear
                );
            }

            return $query
                ->latest('sale_date')
                ->latest('id')
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | LAPORAN PEMBELIAN
        |--------------------------------------------------------------------------
        */

        $query = Purchase::query()
            ->with([
                'supplier',
                'details.product'
            ]);

        /*
        |--------------------------------------------------------------------------
        | FILTER PEMBELIAN
        |--------------------------------------------------------------------------
        */

        if ($this->period === 'daily') {

            $query->whereDate(
                'purchase_date',
                $this->selectedDate
            );

        } elseif ($this->period === 'monthly') {

            $monthDate = Carbon::createFromFormat(
                'Y-m',
                $this->selectedMonth
            );

            $query
                ->whereYear(
                    'purchase_date',
                    $monthDate->year
                )
                ->whereMonth(
                    'purchase_date',
                    $monthDate->month
                );

        } else {

            $query->whereYear(
                'purchase_date',
                $this->selectedYear
            );
        }

        return $query
            ->latest('purchase_date')
            ->latest('id')
            ->get();
    }


    /**
     * Header Excel.
     */
    public function headings(): array
    {
        if ($this->reportType === 'sales') {

            return [
                'No',
                'Kode Transaksi',
                'Tanggal',
                'Produk',
                'Jumlah Item',
                'Total Penjualan',
            ];
        }

        return [
            'No',
            'Kode Transaksi',
            'Tanggal',
            'Supplier',
            'Produk',
            'Jumlah Item',
            'Total Pembelian',
        ];
    }


    /**
     * Mapping setiap baris.
     */
    public function map($transaction): array
    {
        /*
        |--------------------------------------------------------------------------
        | PENJUALAN
        |--------------------------------------------------------------------------
        */

        if ($this->reportType === 'sales') {

            $products = $transaction->saleDetails
                ->map(function ($detail) {

                    return $detail->product->name
                        ?? 'Produk tidak ditemukan';

                })
                ->implode(', ');

            $quantity = $transaction->saleDetails
                ->sum('quantity');

            return [
                $transaction->id,
                $transaction->code,
                $this->formatDate(
                    $transaction->sale_date
                ),
                $products ?: '-',
                $quantity,
                (float) $transaction->total,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | PEMBELIAN
        |--------------------------------------------------------------------------
        */

        $products = $transaction->details
            ->map(function ($detail) {

                return $detail->product->name
                    ?? 'Produk tidak ditemukan';

            })
            ->implode(', ');

        $quantity = $transaction->details
            ->sum('quantity');

        return [
            $transaction->id,
            $transaction->code,
            $this->formatDate(
                $transaction->purchase_date
            ),
            $transaction->supplier->name
                ?? '-',
            $products ?: '-',
            $quantity,
            (float) $transaction->total,
        ];
    }


    /**
     * Format tanggal Indonesia.
     */
    protected function formatDate($date): string
    {
        if (!$date) {
            return '-';
        }

        try {

            return Carbon::parse($date)
                ->format('d/m/Y');

        } catch (\Exception $e) {

            return (string) $date;
        }
    }


    /**
     * Styling Excel.
     */
    public function styles(Worksheet $sheet)
    {
        /*
        |--------------------------------------------------------------------------
        | JUMLAH KOLOM
        |--------------------------------------------------------------------------
        */

        $lastColumn = $this->reportType === 'sales'
            ? 'F'
            : 'G';


        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle(
            "A1:{$lastColumn}1"
        )->applyFromArray([

            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => [
                    'rgb' => 'FFFFFF'
                ],
            ],

            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '1F4E78'
                ],
            ],

            'alignment' => [
                'horizontal' =>
                    Alignment::HORIZONTAL_CENTER,

                'vertical' =>
                    Alignment::VERTICAL_CENTER,

                'wrapText' => true,
            ],

            'borders' => [
                'allBorders' => [
                    'borderStyle' =>
                        Border::BORDER_THIN,

                    'color' => [
                        'rgb' => 'D9E2F3'
                    ],
                ],
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | DATA
        |--------------------------------------------------------------------------
        */

        $highestRow = $sheet->getHighestRow();

        if ($highestRow >= 2) {

            $sheet->getStyle(
                "A2:{$lastColumn}{$highestRow}"
            )->applyFromArray([

                'borders' => [
                    'allBorders' => [
                        'borderStyle' =>
                            Border::BORDER_THIN,

                        'color' => [
                            'rgb' => 'D9D9D9'
                        ],
                    ],
                ],

                'alignment' => [
                    'vertical' =>
                        Alignment::VERTICAL_CENTER,
                ],
            ]);


            /*
            |--------------------------------------------------------------------------
            | NOMOR
            |--------------------------------------------------------------------------
            */

            $sheet->getStyle(
                "A2:A{$highestRow}"
            )->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );


            /*
            |--------------------------------------------------------------------------
            | TANGGAL
            |--------------------------------------------------------------------------
            */

            $sheet->getStyle(
                "C2:C{$highestRow}"
            )->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );


            /*
            |--------------------------------------------------------------------------
            | JUMLAH
            |--------------------------------------------------------------------------
            */

            $quantityColumn =
                $this->reportType === 'sales'
                    ? 'E'
                    : 'F';

            $sheet->getStyle(
                "{$quantityColumn}2:{$quantityColumn}{$highestRow}"
            )->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );


            /*
            |--------------------------------------------------------------------------
            | TOTAL
            |--------------------------------------------------------------------------
            */

            $totalColumn =
                $this->reportType === 'sales'
                    ? 'F'
                    : 'G';

            $sheet->getStyle(
                "{$totalColumn}2:{$totalColumn}{$highestRow}"
            )->getNumberFormat()
                ->setFormatCode(
                    '"Rp" #,##0'
                );

            $sheet->getStyle(
                "{$totalColumn}2:{$totalColumn}{$highestRow}"
            )->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_RIGHT
                );
        }


        /*
        |--------------------------------------------------------------------------
        | TINGGI HEADER
        |--------------------------------------------------------------------------
        */

        $sheet->getRowDimension(1)
            ->setRowHeight(28);


        /*
        |--------------------------------------------------------------------------
        | FREEZE HEADER
        |--------------------------------------------------------------------------
        */

        $sheet->freezePane('A2');


        /*
        |--------------------------------------------------------------------------
        | FILTER EXCEL
        |--------------------------------------------------------------------------
        */

        $sheet->setAutoFilter(
            "A1:{$lastColumn}{$highestRow}"
        );


        /*
        |--------------------------------------------------------------------------
        | RETURN
        |--------------------------------------------------------------------------
        */

        return [
            1 => [
                'font' => [
                    'bold' => true
                ],
            ],
        ];
    }


    /**
     * Lebar kolom.
     */
    public function columnWidths(): array
    {
        if ($this->reportType === 'sales') {

            return [
                'A' => 8,
                'B' => 22,
                'C' => 15,
                'D' => 45,
                'E' => 15,
                'F' => 22,
            ];
        }

        return [
            'A' => 8,
            'B' => 22,
            'C' => 15,
            'D' => 30,
            'E' => 45,
            'F' => 15,
            'G' => 22,
        ];
    }


    /**
     * Nama worksheet.
     */
    public function title(): string
    {
        return $this->reportType === 'sales'
            ? 'Laporan Penjualan'
            : 'Laporan Pembelian';
    }
}