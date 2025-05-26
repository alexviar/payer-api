<?php

namespace App\Exports;

use App\Models\Inspection;
use App\Models\InspectionLot;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCharts;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Chart\Axis;
use PhpOffice\PhpSpreadsheet\Chart\AxisText;
use PhpOffice\PhpSpreadsheet\Chart\ChartColor;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class InspectionExport implements
    FromCollection,
    WithCustomStartCell,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithColumnWidths,
    WithStyles,
    WithEvents,
    WithDrawings,
    WithCharts
{
    protected $inspection;
    protected $customAttributesRange;
    protected $qtyRange;
    protected $rejectsBreakdownRange;
    protected $reworksBreakdownRange;

    public function __construct(Inspection $inspection)
    {
        $inspection->loadMissing('lots.defectInstances', 'lots.reworkInstances');
        $this->inspection = $inspection;
        $customAttributesStartColumn = 'G';
        $customAttributesEndColumn = $this->addColumns($customAttributesStartColumn, $this->inspection->product->attributes->count() - 1);
        $this->customAttributesRange = [$customAttributesStartColumn, $customAttributesEndColumn];

        $qtyStartColumn = $this->addColumns($customAttributesEndColumn, 1);
        $qtyEndColumn = $this->addColumns($qtyStartColumn, 3);
        $this->qtyRange = [$qtyStartColumn, $qtyEndColumn];

        $rejectsBreakdownStartColumn = $this->addColumns($qtyEndColumn, 1);
        $rejectsBreakdownEndColumn = $this->addColumns($rejectsBreakdownStartColumn, $this->inspection->defects->count() - 1);
        $this->rejectsBreakdownRange = [$rejectsBreakdownStartColumn, $rejectsBreakdownEndColumn];

        $reworksBreakdownStartColumn = $this->addColumns($rejectsBreakdownEndColumn, 1);
        $reworksBreakdownEndColumn = $this->addColumns($reworksBreakdownStartColumn, $this->inspection->reworks->count() - 1);
        $this->reworksBreakdownRange = [$reworksBreakdownStartColumn, $reworksBreakdownEndColumn];
    }

    /**
     * Suma un número específico de columnas a una columna dada en formato Excel
     * 
     * @param string $column La columna inicial (A, B, C, ..., Z, AA, AB, etc.)
     * @param int $n El número de columnas a sumar
     * @return string La columna resultante después de sumar n columnas
     */
    protected function addColumns(string $column, int $n): string
    {
        // Convertir la columna a su valor numérico (A=1, B=2, ..., Z=26, AA=27, etc.)
        $columnIndex = 0;
        $length = strlen($column);

        for ($i = 0; $i < $length; $i++) {
            $columnIndex = $columnIndex * 26 + (ord($column[$i]) - ord('A') + 1);
        }

        // Sumar n al índice
        $columnIndex += $n;

        // Convertir el nuevo índice de vuelta a formato de letra
        $result = '';

        while ($columnIndex > 0) {
            $modulo = ($columnIndex - 1) % 26;
            $result = chr(ord('A') + $modulo) . $result;
            $columnIndex = (int)(($columnIndex - $modulo) / 26);
        }

        return $result;
    }

    public function collection()
    {
        return $this->inspection->lots;
    }

    protected $startCell = 'A30';
    public function startCell(): string
    {
        return $this->startCell;
    }

    public function headings(): array
    {
        $headings = [
            ['',   '',   '',               '',                         '',               ''],
            ['Nº', 'QN', __('Month'), __('Inspection Date'), __('Shift'), 'NP']
        ];

        foreach ($this->inspection->product->attributes as $attribute) {
            $headings[0][] = '';
            $headings[1][] = $attribute->name;
        }

        $headings[0] = array_merge($headings[0], ['QTY',                   '',                        '',                        '']);
        $headings[1] = array_merge($headings[1], [__('Total Sorted'), __('Total Accepted'), __('Total Rejected'), __('Total Reworked')]);

        $rejects = $this->inspection->defects->collect();
        $reject = $rejects->pop();
        $headings[0][] = __('Rejects breakdown');
        $headings[1][] = $reject?->name ?? 'N/A';
        foreach ($rejects as $reject) {
            $headings[0][] = '';
            $headings[1][] = $reject->name;
        }

        $reworks = $this->inspection->reworks->collect();
        $rework = $reworks->pop();
        $headings[0][] = __('Reworks breakdown');
        $headings[1][] = $rework?->name ?? 'N/A';
        foreach ($reworks as $rework) {
            $headings[0][] = '';
            $headings[1][] = $rework->name;
        }

        $this->startCell = 'A32';

        return $headings;
    }

    private $rowNumber = 1;
    public function map($lot): array
    {
        /**
         * @var InspectionLot $lot
         */
        $row = [
            $this->rowNumber++, // <-- Row Index
            $lot->qn,
            Str::upper($lot->inspect_date->format('M')),
            Str::upper($lot->inspect_date->format('M/d/Y')),
            $lot->shift,
            $lot->pn
        ];

        foreach ($this->inspection->product->attributes as $attribute) {
            $row[] = $lot->attributes->where('id', $attribute->id)->first()?->pivot->value ?? '';
        }

        $row[] = $lot->total_units;
        $row[] = (string) $lot->total_units - $lot->total_rejects;
        $row[] = (string) $lot->total_rejects;
        $row[] = (string) $lot->reworkInstances->count();

        $defects = $this->inspection->defects->collect();
        $defect = $defects->pop();
        $row[] = $defect ? (string) $lot->defectInstances->where('defect_id', $defect->id)->count() : '';
        foreach ($defects as $defect) {
            $row[] = (string) $lot->defectInstances->where('defect_id', $defect->id)->count();
        }

        $reworks = $this->inspection->reworks->collect();
        $rework = $reworks->pop();
        $row[] = $rework ? (string) $lot->reworkInstances->where('rework_id', $rework->id)->count() : '';
        foreach ($reworks as $rework) {
            $row[] = (string) $lot->reworkInstances->where('rework_id', $rework->id)->count();
        }

        return $row;
    }

    public function columnWidths(): array
    {
        $columnWidths = [
            'B' => 15,
            'D' => 15,
            'E' => 15
        ];
        $startColumn = $this->customAttributesRange[0];
        $endColumn = $this->addColumns($this->reworksBreakdownRange[1], 1);
        $currentColumn = $startColumn;
        while ($currentColumn != $endColumn) {
            $columnWidths[$currentColumn] = 15;
            $currentColumn = $this->addColumns($currentColumn, 1);
        }

        return $columnWidths;
    }

    public function styles(Worksheet $sheet)
    {
        $defaultStyle = $sheet->getParent()->getDefaultStyle();
        $defaultStyle->getFill()->setFillType(
            \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID
        )->getStartColor()->setARGB('FF000000');
        $defaultStyle->getFont()->getColor()->setARGB('FFFFFFFF');

        [$startColumn, $endColumn] = $this->qtyRange;
        $sheet->mergeCells("{$startColumn}30:{$endColumn}30");

        [$startColumn, $endColumn] = $this->rejectsBreakdownRange;
        $sheet->mergeCells("{$startColumn}30:{$endColumn}30");

        [$startColumn, $endColumn] = $this->reworksBreakdownRange;
        $sheet->mergeCells("{$startColumn}30:{$endColumn}30");

        for ($i = ord('A'); $i < ord($this->qtyRange[0]); $i++) {
            $column = chr($i);
            $sheet->mergeCells("{$column}30:{$column}31", Worksheet::MERGE_CELL_CONTENT_MERGE);
        }

        $this->styleValues($sheet);

        return [
            30 => [
                'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => '00B050']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ],
            31 => [
                'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => '00B050']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ]
        ];
    }

    private function styleValues(Worksheet $sheet)
    {
        $endRow = $sheet->getHighestRow();
        $sheet->getStyle("A32:{$this->reworksBreakdownRange[1]}{$endRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED,
                    'color' => ['rgb' => '00B050']
                ]
            ],
        ]);
        $this->styleTotalAcceptedAndRejectedValues($sheet);
    }

    private function styleTotalAcceptedAndRejectedValues(Worksheet $sheet)
    {
        // Determinar las columnas de 'Total Accepted' y 'Total Rejected'
        [$qtyStartColumn, $qtyEndColumn] = $this->qtyRange;
        $totalAcceptedColumn = $this->addColumns($qtyStartColumn, 1); // Segunda columna de QTY
        $totalRejectedColumn = $this->addColumns($qtyStartColumn, 2); // Tercera columna de QTY
        $rowStart = 32; // Primer fila de datos
        $rowEnd = $sheet->getHighestRow();
        // Aplicar color verde a 'Total Accepted'
        for ($row = $rowStart; $row <= $rowEnd; $row++) {
            $sheet->getStyle("{$totalAcceptedColumn}{$row}")
                ->getFont()->getColor()->setRGB('00FF00');
        }
        // Aplicar color rojo a 'Total Rejected'
        for ($row = $rowStart; $row <= $rowEnd; $row++) {
            $sheet->getStyle("{$totalRejectedColumn}{$row}")
                ->getFont()->getColor()->setRGB('FF0000');
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LEGAL);
                $sheet->getPageMargins()
                    ->setTop(0.5)
                    ->setRight(0.5)
                    ->setLeft(0.5)
                    ->setBottom(0.5)
                    ->setHeader(0)
                    ->setFooter(0);

                $this->writeGeneralInfo($sheet);

                $this->summarizeData($sheet);

                $this->writeInspectionResult($sheet);

                $this->writeDateInfo($sheet);

                $this->writeAdditionalInfo($sheet);
            }
        ];
    }

    private function writeAdditionalInfo(Worksheet $sheet)
    {
        $data = [
            [
                "rowSpan" => 3,
                "value" => "Location: Av. Ejército Nacional 5911, Montebello, 32398 Juárez, Chih. Cd Juárez, Chih. Phone: +52 656 221 7366"
            ],
            [
                "rowSpan" => 4,
                "value" => "Location where we can provide the service: CHIHUAHUA, TORREON, DURANGO, TAMAULIPAS, REYNOSA, SAN BLAS, MONTERREY, EL PASO TX, NEW MEXICO."
            ]

        ];

        $startRow = 8;
        foreach ($data as $row) {
            $endRow = $startRow + $row['rowSpan'] - 1;
            $sheet->setCellValue("M{$startRow}", $row['value']);
            $sheet->mergeCells("M{$startRow}:T{$endRow}");
            $sheet->getStyle("M{$startRow}:T{$endRow}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '00B050']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ],
            ]);
            $startRow = $endRow + 1;
        }
    }

    private function writeDateInfo(Worksheet $sheet)
    {
        $dates = [
            [
                'label' => __('START DATE'),
                'value' => $this->inspection->start_date?->format('M/d/Y') ?? '-',
            ],
            [
                'label' => __('FINISH DATE'),
                'value' => $this->inspection->complete_date?->format('M/d/Y') ?? '-',
            ],
        ];

        $startCol = 'L';
        foreach ($dates as $date) {
            $sheet->setCellValue("{$startCol}3", $date['label']);
            $endCol = $this->addColumns($startCol, 1);
            $sheet->mergeCells("{$startCol}3:{$endCol}3");
            $sheet->getStyle("{$startCol}3:{$endCol}3")->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ],
            ]);
            $startCol = $this->addColumns($endCol, 1);
            $sheet->setCellValue("{$startCol}3", $date['value']);
            $endCol = $this->addColumns($startCol, 1);
            $sheet->mergeCells("{$startCol}3:{$endCol}3");
            $sheet->getStyle("{$startCol}3:{$endCol}3")->applyFromArray([
                'font' => ['bold' => true],
                'borders' => [
                    'bottom' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED,
                        'color' => ['rgb' => '00B050']
                    ]
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ],
            ]);
            $startCol = $this->addColumns($endCol, 1);
        }
    }

    private function writeInspectionResult(Worksheet $sheet)
    {
        $sheet->setCellValue('G1', __('REPORT OF DAILY RESULTS'));
        $sheet->mergeCells('G1:K6');
        $sheet->getStyle('G1:K6')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 24,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
        ]);

        $totalInspected = $this->inspection->total_approved + $this->inspection->total_rejected;
        $data = [
            __('TOTAL INSPECTED') => $totalInspected,
            __('PENDING QUANTITY FOR INSPECTION') => $this->inspection->inventory - $totalInspected,
            __('TOTAL ACCEPTED') => $this->inspection->total_approved,
            __('TOTAL REJECTED') => $this->inspection->total_rejected,
            __('% REJECTED') => '=K13/K7',
        ];

        $rowIndex = 7;
        foreach ($data as $label => $value) {
            $sheet->setCellValue("G{$rowIndex}", $label);
            $sheet->mergeCells("G{$rowIndex}:J{$rowIndex}");

            $sheet->setCellValue("K{$rowIndex}", $value);

            $sheet->getStyle("G{$rowIndex}:K{$rowIndex}")->applyFromArray([
                'font' => ['bold' => true],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '00B050']
                    ]
                ],
            ]);
            $rowIndex += 2;
        }
        $sheet->getStyle("K15")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
    }

    private function summarizeData(Worksheet $sheet)
    {
        // Determinar la última fila de datos
        $lastDataRow = $sheet->getHighestRow();
        $sumRow = $lastDataRow + 3;
        $labelRow = $sumRow + 1;

        $sheet->getRowDimension($sumRow)->setRowHeight(30);
        $sheet->getRowDimension($labelRow)->setRowHeight(30);
        // Sumar QTY
        $colStart = $this->qtyRange[0];
        $colEnd = $this->reworksBreakdownRange[1];
        for ($col = $colStart; $col <= $colEnd; $col = $this->addColumns($col, 1)) {
            $sheet->setCellValue("{$col}{$sumRow}", "=SUM({$col}32:{$col}{$lastDataRow})");
            $sheet->getStyle("{$col}{$sumRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => '00B050']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ],
            ]);
            $sheet->setCellValue("{$col}{$labelRow}", "={$col}31");
            $sheet->getStyle("{$col}{$labelRow}")->applyFromArray([
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ],
            ]);
        }
    }

    private function writeGeneralInfo(Worksheet $sheet)
    {
        $data = [
            __('CUSTOMER NAME') => $this->inspection->client->name,
            __('CUSTOMER/USER PN') => $this->pn ?? '-',
            __('PART NAME') => $this->inspection->client->part_name ?? '-',
            __('SALES AGENT') => $this->inspection->salesAgents->pluck('name')->join("\n") ?: '-',
            __('AUTHORIZED INVENTORY') => $this->inspection->inventory,
            __('COMPANY') => $this->inspection->client->name,
            __('LOCATION') => $this->inspection->client->address,
            __('QN APPLICABLE') => $this->qn ?? '-',
        ];

        $rowIndex = 7;
        foreach ($data as $label => $value) {
            $sheet->setCellValue("B{$rowIndex}", $label . ':');
            $sheet->mergeCells("B{$rowIndex}:C{$rowIndex}");
            $sheet->setCellValue("D{$rowIndex}", $value);
            $sheet->mergeCells("D{$rowIndex}:E{$rowIndex}");
            $sheet->getStyle("D{$rowIndex}:E{$rowIndex}")->applyFromArray([
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'bottom' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED,
                        'color' => ['rgb' => '00B050']
                    ]
                ]
            ]);
            $rowIndex += 2;
        }

        $sheet->setCellValue("B{$rowIndex}", __('WORK DESCRIPTION') . ':');
        $sheet->mergeCells("B{$rowIndex}:C{$rowIndex}");
        $rowIndex++;
        $sheet->setCellValue("B{$rowIndex}", $this->inspection->description);
        $endIndex = $rowIndex + 3;
        $sheet->mergeCells("B{$rowIndex}:E{$endIndex}");
        $sheet->getStyle("B{$rowIndex}:E{$endIndex}")->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '00FF00']
                ]
            ]
        ]);
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath(public_path('logo.png'));
        $drawing->setHeight(70);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(60);
        $drawing->setOffsetY(25);
        return $drawing;
    }

    public function charts()
    {
        $highestRow = 31 + $this->collection()->count();
        $valuesRow = $highestRow + 3;
        $categoriesRow = $valuesRow + 1;
        $charts = [];

        // Rejects Breakdown Chart
        if (!$this->inspection->defects->isEmpty()) {
            $title = __('Rejects Rate Breakdown');
            $startColumn = $this->rejectsBreakdownRange[0];
            $endColumn = $this->rejectsBreakdownRange[1];
            $charts[] = $this->makeChart($title, $startColumn, $endColumn, $categoriesRow, $valuesRow);
        }

        // Rework Breakdown Chart
        if (!$this->inspection->reworks->isEmpty()) {
            $title = __('Reworks Rate Breakdown');
            $startColumn = $this->reworksBreakdownRange[0];
            $endColumn = $this->reworksBreakdownRange[1];
            $charts[] = $this->makeChart($title, $startColumn, $endColumn, $categoriesRow, $valuesRow);
        }
        return $charts;
    }

    private function makeChart($title, $startColumn, $endColumn, $categoriesRow, $valuesRow)
    {
        $label        = [new DataSeriesValues('String', "Worksheet!\${$startColumn}\$30", null, 1)];
        $categories   = [new DataSeriesValues('String', "Worksheet!\${$startColumn}\${$categoriesRow}:\${$endColumn}\${$categoriesRow}", null, $this->inspection->defects->count())];
        $values       = [new DataSeriesValues('Number', "Worksheet!\${$startColumn}\${$valuesRow}:\${$endColumn}\${$valuesRow}", null, $this->inspection->defects->count())];

        $values[0]->setFillColor('1A1A1A');
        $values[0]->setLineColorProperties('00B050');

        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_STANDARD,
            range(0, count($values) - 1),
            $label,
            $categories,
            $values
        );
        $series->setPlotDirection(DataSeries::DIRECTION_COL);

        $layout = new Layout();
        $layout->setShowVal(true);
        $layout->setLabelFontColor(new ChartColor('FFFFFF'));
        $plotArea = new PlotArea($layout, [$series]);
        $plotArea->setNoFill(true);

        $titleRichText = new RichText();
        $run = $titleRichText->createTextRun($title);
        $run->getFont()->setChartColor(['value' => 'FFFFFF']);
        $run->getFont()->setSize(12)->setBold(true);
        $titleObj = new Title($titleRichText);

        $yAxis = new Axis();
        $yAxis->setAxisOption('hidden', true);

        $xAxisText = new AxisText();
        $xAxisText->getFillColorObject()->setValue('FFFFFF')->setType(ChartColor::EXCEL_COLOR_TYPE_RGB);
        $xAxis = new Axis();
        $xAxis->setAxisText($xAxisText);

        $chart = new Chart(
            $title,
            $titleObj,
            null,
            $plotArea,
            yAxis: $yAxis,
            xAxis: $xAxis,
        );

        // $chart->setNoFill(true);
        $chart->getFillColor()->setColorProperties('1A1A1A', null, ChartColor::EXCEL_COLOR_TYPE_RGB);
        $chart->setTopLeftPosition("{$startColumn}15");
        $chart->setBottomRightPosition("{$endColumn}28", 100);

        return $chart;
    }
}
