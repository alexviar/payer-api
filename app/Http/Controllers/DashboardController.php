<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Inspection;
use App\Models\Plant;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Total de inspecciones
        $totalInspections = Inspection::count();

        // Total de plantas
        $totalPlants = Plant::count();

        // Estado general de inspecciones
        $inspectionStatus = Inspection::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $this->getStatusText($item->status) => $item->total
                ];
            });

        // Nuevas inspecciones en los últimos 30 días
        $newInspections = Inspection::where('created_at', '>=', $thirtyDaysAgo)->count();

        // Nuevos clientes en los últimos 30 días
        $newClients = Client::where('created_at', '>=', $thirtyDaysAgo)->count();

        // Nuevos productos en los últimos 30 días
        $newProducts = Product::where('created_at', '>=', $thirtyDaysAgo)->count();

        // Tiempo promedio de inspección (en días)
        $averageInspectionTime = Inspection::whereNotNull('start_date')
            ->whereNotNull('complete_date')
            ->selectRaw('AVG(DATEDIFF(complete_date, start_date)) as avg_days')
            ->first()
            ->avg_days;

        return response()->json([
            'total_inspections' => $totalInspections,
            'total_plants' => $totalPlants,
            'inspection_status' => $inspectionStatus,
            'new_inspections_30_days' => $newInspections,
            'new_clients_30_days' => $newClients,
            'new_products_30_days' => $newProducts,
            'average_inspection_time_days' => round($averageInspectionTime, 1),
        ]);
    }

    private function getStatusText(int $status): string
    {
        return match ($status) {
            Inspection::PENDING_STATUS => 'Pendiente',
            Inspection::ACTIVE_STATUS => 'Activo',
            Inspection::ON_HOLD_STATUS => 'En Espera',
            Inspection::UNDER_REVIEW_STATUS => 'En Revisión',
            Inspection::COMPLETED_STATUS => 'Completado',
            default => 'Desconocido',
        };
    }
}
