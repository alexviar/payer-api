<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function generateReport(Request $request, Inspection $inspection)
    {
        $inspection->load(['lots' => function ($query) use ($request) {
            $query->with(['attributes', 'defectInstances', 'reworkInstances']);
            $request->whenFilled('date_from', function ($value) use ($query) {
                $query->where('inspect_date', '>=', $value);
            });
            $request->whenFilled('date_to', function ($value) use ($query) {
                $query->where('inspect_date', '<=', $value);
            });
        }]);

        if ($request->get('format') === 'pdf') {
            return $this->generatePdfReport($inspection, $request->get('date_from'), $request->get('date_to'));
        }
        if ($request->get('format') === 'xlsx') {
            return $this->generateExcelReport($inspection);
        }
        return response()->json(['message' => 'Formato de reporte no válido'], 400);
    }
    private function generateExcelReport(Inspection $inspection)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\InspectionExport($inspection),
            'inspeccion_' . $inspection->id . '.xlsx'
        );
    }

    private function generatePdfReport(Inspection $inspection, $dateFrom, $dateTo)
    {
        $now = now();
        $dateFrom = $dateFrom ? Date::parse($dateFrom) : $inspection->start_date;
        $dateTo = $dateTo ? Date::parse($dateTo) : $inspection->complete_date ?? $now;
        $reportData = [
            'date_from' => $dateFrom?->format('d-m-Y') ?? '',
            'date_to' => $dateTo?->format('d-m-Y') ?? '',
            'week' => ($dateFrom ?? $dateTo ?? $now)?->weekOfYear,
            'month' => ($dateFrom ?? $dateTo ?? $now)->format('M'),
            'mission_info' => [
                'report_type' => 'Informe de la misión',
                'mission' => '',
                'reference' => '',
                'recipient_name' => $inspection->client->representative,
                'distribution_list' => "{$inspection->client->name}\n{$inspection->client->email}",
                'date' => $now->format('d-m-Y'),
                'time' => $now->format('H:i'),
                'location' => 'JUAREZ'
            ],
            'mission_properties' => [
                'service_type' => 'INSPECTION - corrective sorting & basic rework / containment',
                'start_date' => $inspection->start_date?->format('d-m-Y') ?? 'No iniciado',
                'status' => $inspection->status_text,
                'plant' => $inspection->plant->name,
                'accepted_quantity' => $inspection->total_approved,
                'rejected_quantity' => $inspection->total_rejected,
                'QN' => $inspection->qn,
                'supervisor' => $inspection->groupLeader->name
            ],
            'statistics' => [
                'week_in_progress' => $inspection->total_reworked,
                'month_in_progress' => $inspection->total_reworked,
                'total_mission' => $inspection->total_reworked,
                'reworked_pieces' => $inspection->total_reworked,
                'total_pieces' => $inspection->total_approved + $inspection->total_rejected,
            ],
            'parts_headers' => [
                'index' => '#',
                'custom_attributes' => $inspection->product->attributes->mapWithKeys(fn($attribute) => [
                    $attribute->id => $attribute->name,
                ]),
                'accepted' => 'Aceptadas',
                'rejected' => 'Rechazadas',
                'reject_percentage' => 'Porcentaje de rechazo'
            ],
            'parts_data' => $inspection->lots->map(function ($lot, $index) use ($inspection) {
                $customAttributes = $inspection->product->attributes;
                $customAttributesValues = $customAttributes->mapWithKeys(fn($attribute) => [
                    $attribute->id => $lot->attributes->where('id', $attribute->id)->first()?->pivot->value,
                ]);
                return [
                    'index' => $index + 1,
                    'custom_attributes' => $customAttributesValues,
                    'accepted' => $lot->total_units - $lot->total_rejects,
                    'rejected' => $lot->total_rejects,
                    'reject_percentage' => ($lot->total_units ? number_format($lot->total_rejects / $lot->total_units * 100, 2) : ' - ') . '%',
                    'reworked' => $lot->total_reworks,
                ];
            }),
            'defect_images' => $inspection->lots->reduce(function ($items, $lot) {
                foreach ($lot->defectInstances as $instance) {
                    if ($instance->include_in_report) {
                        $items[] = [
                            'date' => $lot->inspect_date->format('d-m-Y'),
                            'defect_name' => $instance->defect->name,
                            'photos' => Arr::map($instance->evidences, fn($evidence) => $this->resizeTmp(Storage::path($evidence), 120, 160)),
                        ];
                    }
                }

                return $items;
            }, []),
            'signer' => [
                'name' => 'Ing. Carlos Alonso Carrera',
                'title' => 'Payer sorting desk expert',
                'email' => 'qualcontrol@industrialjuarez.com'
            ]
        ];

        // Generate chart URLs
        $chartUrls = $this->generateChartUrls($reportData);

        if (request('format') === 'pdf') {
            set_time_limit(3000);
            $pdf = Pdf::loadView('reports.mission-report', compact('reportData', 'chartUrls'));
            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream('mission-report.pdf');
        }

        return view('reports.mission-report', compact('reportData', 'chartUrls'));
    }

    private function generateChartUrls($data)
    {
        $baseUrl = 'https://quickchart.io/chart';

        $reworked_pieces = $data['statistics']['reworked_pieces'];
        $total_pieces = $data['statistics']['total_pieces'];
        $ppm = $total_pieces ? number_format($reworked_pieces / $total_pieces * 1000000, 0, thousands_separator: ' ') : ' - ';

        // Donut chart for reworked pieces
        $donutConfig = [
            'type' => 'doughnut',
            'data' => [
                'datasets' => [[
                    'data' => [$reworked_pieces, $total_pieces - $reworked_pieces],
                    'backgroundColor' => ['#408833', '#40883320'],
                    'borderWidth' => 0
                ]],
                'labels' => ['Retrabajadas', 'Restantes']
            ],
            'options' => [
                'circumference' => pi(),
                'rotation' => pi(),
                'cutoutPercentage' => 65,
                'plugins' => [
                    'datalabels' => [
                        'display' => false,
                    ],
                    'doughnutlabel' => [
                        'labels' => [[
                            'text' => "\n\n\n\n\n" . $ppm . ' PPM',
                            'font' => ['size' => 16, 'weight' => 'bold'],
                            'color' => '#000',
                        ], [
                            'text' => "\n\n\n\n\n\n\n(total)",
                            'font' => ['size' => 12],
                            'color' => '#666'
                        ]]
                    ]
                ],
                'legend' => ['display' => false],
                'maintainAspectRatio' => false
            ]
        ];

        // Pareto chart (combination chart)
        $paretoData = [];
        $paretoLabels = [];
        $cumulativeData = [];
        $total = 0;

        foreach ($data['parts_data'] as $part) {
            $paretoLabels[] = $part['index'];
            $paretoData[] = (int)$part['reworked'];
            $total += (int)$part['reworked'];
        }

        // Sort data for Pareto
        arsort($paretoData);
        $sortedLabels = [];
        $sortedData = [];
        $cumulative = 0;

        foreach ($paretoData as $key => $value) {
            $sortedLabels[] = $paretoLabels[$key];
            $sortedData[] = $value;
            $cumulative += $value;
            $cumulativeData[] = $total ? ($cumulative / $total) * 100 : 0;
        }

        $paretoConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $sortedLabels,
                'datasets' => [
                    [
                        'type' => 'line',
                        'label' => 'Cumulativa %',
                        'data' => $cumulativeData,
                        'borderColor' => '#4bce35',
                        'backgroundColor' => '#4bce35',
                        'yAxisID' => 'y1',
                        'fill' => false
                    ],
                    [
                        'type' => 'line',
                        'label' => '80/20',
                        'data' => array_fill(0, 8, 80),
                        'borderColor' => '#666',
                        'backgroundColor' => '#666',
                        'borderDash' => [5, 5],
                        'yAxisID' => 'y1',
                        'pointRadius' => 0,
                        'fill' => false
                    ],
                    [
                        'type' => 'bar',
                        'label' => 'Retrabajo',
                        'data' => $sortedData,
                        'backgroundColor' => '#408833',
                        'yAxisID' => 'y'
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'interaction' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
                'scales' => [
                    'xAxes' => [[
                        'ticks' => [
                            'maxRotation' => 45,
                            'minRotation' => 45,
                            'fontSize' => 8
                        ]
                    ]],
                    'yAxes' => [
                        [
                            'id' => 'y',
                            'type' => 'linear',
                            'display' => true,
                            'position' => 'left',
                            'ticks' => [
                                'beginAtZero' => true,
                                'max' => $cumulative,
                                'fontSize' => 8
                            ]
                        ],
                        [
                            'id' => 'y1',
                            'type' => 'linear',
                            'display' => true,
                            'position' => 'right',
                            'ticks' => [
                                'beginAtZero' => true,
                                'max' => 100,
                                'fontSize' => 8,
                                'callback' => '%%callback%%'
                            ],
                            'gridLines' => [
                                'drawOnChartArea' => false
                            ]
                        ]
                    ]
                ],
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'font' => [
                            'size' => 8
                        ]
                    ]
                ]
            ]
        ];

        return [
            'donut' => $baseUrl . '?c=' . urlencode(json_encode($donutConfig)) . '&w=300&h=200',
            'pareto' => $baseUrl . '?c=' . urlencode(str_replace('"%%callback%%"', 'function(value) { return value + "%"; }', json_encode($paretoConfig))) . '&w=500&h=300'
        ];
    }

    private function resizeTmp($sourcePath, $targetWidth, $targetHeight)
    {
        // $tmpPath = tempnam(sys_get_temp_dir(), 'resized_');
        // $targetPath = $tmpPath . '.png';
        // rename($tmpPath, $targetPath);
        $targetPath = \Illuminate\Support\Str::beforeLast($sourcePath, '.') . '_resized.png';
        if (file_exists($targetPath)) return $targetPath;

        $this->resize($sourcePath, $targetPath, $targetWidth, $targetHeight);

        // chmod($targetPath, 0644);

        return $targetPath;
    }

    private function resize($sourcePath, $targetPath, $targetWidth, $targetHeight)
    {
        // Obtener tipo y tamaño de la imagen original
        [$srcWidth, $srcHeight, $imageType] = getimagesize($sourcePath);

        // Crear recurso de imagen desde el archivo según su tipo
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $srcImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $srcImage = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $srcImage = imagecreatefromgif($sourcePath);
                break;
            default:
                throw new \Exception("Unsupported image type.");
        }

        // Calcular nuevas dimensiones manteniendo la proporción
        $srcRatio = $srcWidth / $srcHeight;
        $targetRatio = $targetWidth / $targetHeight;

        if ($srcRatio > $targetRatio) {
            $newWidth = $targetWidth;
            $newHeight = intval($targetWidth / $srcRatio);
        } else {
            $newHeight = $targetHeight;
            $newWidth = intval($targetHeight * $srcRatio);
        }

        // Crear imagen destino con fondo transparente
        $dstImage = imagecreatetruecolor($targetWidth, $targetHeight);
        imagesavealpha($dstImage, true);
        $transparent = imagecolorallocatealpha($dstImage, 0, 0, 0, 127);
        imagefill($dstImage, 0, 0, $transparent);

        // Calcular posición centrada
        $dstX = intval(($targetWidth - $newWidth) / 2);
        $dstY = intval(($targetHeight - $newHeight) / 2);

        // Redimensionar y copiar imagen original en el centro del canvas
        imagecopyresampled($dstImage, $srcImage, $dstX, $dstY, 0, 0, $newWidth, $newHeight, $srcWidth, $srcHeight);

        // Guardar como PNG para mantener la transparencia
        imagepng($dstImage, $targetPath);

        // Liberar memoria
        imagedestroy($srcImage);
        imagedestroy($dstImage);
    }
}
