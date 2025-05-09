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

        $query->with(['plant', 'product.client', 'groupLeader', 'salesAgents', 'defects', 'reworks']);

        $query->latest('id');

        /** @var LengthAwarePaginator $result */
        $result = $query->paginate($request->input('page_size'));
        $result->getCollection()->each(fn($inspection) => $inspection->append('client'));

        return $result;
    }

    public function show(Inspection $inspection)
    {
        return $inspection;
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
