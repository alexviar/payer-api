<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\InspectionLot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InspectionLotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Inspection $inspection)
    {
        $query = $inspection->lots()
            ->with(['attributes', 'defectInstances', 'defectInstances.defect']);

        $query->latest('id');
        return $query->paginate($request->input('per_page'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Inspection $inspection)
    {
        return DB::transaction(function () use ($request, $inspection) {
            $lot = $inspection->lots()->create([
                'total_rejects' => 0,
                'total_reworks' => 0,
            ] + $request->all());
            $lot->attributes()->sync($request->input('attributes', []));
            return $lot;
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(InspectionLot $inspectionLot)
    {
        $inspectionLot->loadMissing(['attributes', 'inspection']);
        return $inspectionLot;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InspectionLot $inspectionLot)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InspectionLot $inspectionLot)
    {
        $inspectionLot->update($request->all());
        $inspectionLot->attributes()->sync(
            collect($request->input('attributes', []))
                ->mapWithKeys(fn($attribute) => [$attribute['custom_attribute_id'] => ['value' => $attribute['value']]])
        );
        return $inspectionLot;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InspectionLot $inspectionLot)
    {
        //
    }
}
