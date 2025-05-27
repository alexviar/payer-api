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

        $query->with(['lastReview', 'plant', 'product.client', 'product.attributes', 'groupLeader', 'salesAgents', 'defects', 'reworks']);
        $query->withCount('reviews');
        $query->latest('id');

        $query->when($request->input('filter.status'), function ($query, $status) {
            return $query->whereIn('status', Arr::wrap($status));
        });

        $query->when($request->input('search'), function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('product', function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                })->orWhereHas('product.client', function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                })->orWhere('id', $search);
            });
        });

        /** @var LengthAwarePaginator $result */
        $result = $query->paginate($request->input('page_size'));
        $result->getCollection()->each(fn($inspection) => $inspection->append('client'));

        return $result;
    }

    public function show(Inspection $inspection)
    {
        $inspection->load(['lastReview', 'plant', 'product.client', 'product.attributes', 'groupLeader', 'salesAgents', 'defects', 'reworks']);
        $inspection->loadCount('reviews');
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
                \Maatwebsite\Excel\Excel::MPDF
            );
        } else {
            abort(400, 'Invalid format');
        }
    }

    public function store(Request $request)
    {
        $payload = $request->all();
        $inspection = DB::transaction(function () use ($payload) {
            $status = Arr::get($payload, 'status');
            if ($status === Inspection::ACTIVE_STATUS) {
                $payload['start_date'] = now();
            }
            if ($status === Inspection::COMPLETED_STATUS) {
                $payload['start_date'] = now();
                $payload['complete_date'] = now();
            }

            /** @var Inspection $inspection */
            $inspection = Inspection::create(Arr::except($payload, ['sales_agent_ids', 'defect_ids', 'rework_ids']));
            $inspection->salesAgents()->sync($payload['sales_agent_ids']);
            $inspection->defects()->sync($payload['defect_ids']);
            $inspection->reworks()->sync($payload['rework_ids']);
        });


        return response()->json($inspection, 201);
    }

    public function update(Request $request, Inspection $inspection)
    {
        $payload = $request->all();
        DB::transaction(function () use ($inspection, $payload) {
            $status = Arr::get($payload, 'status');
            if ($status === Inspection::ACTIVE_STATUS && $inspection->start_date === null) {
                $payload['start_date'] = now();
            }
            if ($status === Inspection::COMPLETED_STATUS && $inspection->complete_date === null) {
                $payload['complete_date'] = now();
            }

            /** @var Inspection $inspection */
            $inspection->update(Arr::except($payload, ['sales_agent_ids', 'defect_ids', 'rework_ids']));
            $inspection->salesAgents()->sync($payload['sales_agent_ids']);
            $inspection->defects()->sync($payload['defect_ids']);
            $inspection->reworks()->sync($payload['rework_ids']);
        });


        return $inspection;
    }
}
