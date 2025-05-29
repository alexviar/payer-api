<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Inspection;
use App\Models\Plant;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Total inspections and plants
        $totalInspections = Inspection::count();
        $totalPlants = Plant::count();

        // Inspection status breakdown
        $inspectionStatus = Inspection::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->total];
            });

        // New records in last 30 days
        $newInspections = Inspection::where('created_at', '>=', $thirtyDaysAgo)->count();
        $newClients = Client::where('created_at', '>=', $thirtyDaysAgo)->count();
        $newProducts = Product::where('created_at', '>=', $thirtyDaysAgo)->count();

        // Average inspection time (in days)
        $averageInspectionTime = Inspection::whereNotNull('start_date')
            ->whereNotNull('complete_date')
            ->selectRaw('AVG(DATEDIFF(complete_date, start_date)) as avg_days')
            ->first()
            ->avg_days;

        // Percentage of inspections without reviews
        $completedInspections = Inspection::where('status', Inspection::COMPLETED_STATUS)->count();
        $inspectionsWithoutReviews = Inspection::where('status', Inspection::COMPLETED_STATUS)
            ->whereDoesntHave('reviews')
            ->count();

        $perfectInspectionsPercentage = $completedInspections > 0
            ? ($inspectionsWithoutReviews / $completedInspections) * 100
            : null;

        return response()->json([
            'total_inspections' => $totalInspections,
            'total_plants' => $totalPlants,
            'inspection_status' => $inspectionStatus,
            'new_inspections_30_days' => $newInspections,
            'new_clients_30_days' => $newClients,
            'new_products_30_days' => $newProducts,
            'average_inspection_time_days' => round($averageInspectionTime, 2),
            'perfect_inspections_percentage' => $perfectInspectionsPercentage !== null ? round($perfectInspectionsPercentage, 2) : null,
        ]);
    }
}
