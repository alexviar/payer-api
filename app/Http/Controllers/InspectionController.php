<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class InspectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Inspection::query();

        $query->with(['plant', 'product.client', 'product.attributes', 'groupLeader', 'salesAgents', 'defects', 'reworks']);

        $query->latest('id');

        /** @var LengthAwarePaginator $result */
        $result = $query->paginate($request->input('page_size'));
        $result->getCollection()->each(fn($inspection) => $inspection->append('client'));

        return $result;
    }

    public function show(Inspection $inspection)
    {
        $inspection->load(['plant', 'product.client', 'product.attributes', 'groupLeader', 'salesAgents', 'defects', 'reworks']);
        $inspection->append('client');
        return $inspection;
    }

    public function downloadReport(Request $request, Inspection $inspection)
    {
        if ($request->get('format') == 'xlsx') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\InspectionExport($inspection),
                'inspeccion_' . $inspection->id . '.xlsx'
            );
        } else if ($request->get('format') == 'pdf') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\InspectionExport($inspection),
                'inspeccion_' . $inspection->id . '.pdf',
                \Maatwebsite\Excel\Excel::DOMPDF
            );
        } else {
            abort(400, 'Invalid format');
        }
    }

    public function store(Request $request)
    {
        $payload = $request->all();
        $inspection = DB::transaction(function () use ($payload) {

            /** @var Inspection $inspection */
            $inspection = Inspection::create(Arr::except($payload, ['sales_agent_ids', 'defect_ids', 'rework_ids']));
            $inspection->salesAgents()->sync($payload['sales_agent_ids']);
            $inspection->defects()->sync($payload['defect_ids']);
            $inspection->reworks()->sync($payload['rework_ids']);
        });


        return response()->json($inspection, 201);
    }
}
