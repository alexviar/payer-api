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

        $request->whenFilled('filter.date_from', function ($value) use ($query) {
            $query->where('inspect_date', '>=', $value);
        });
        $request->whenFilled('filter.date_to', function ($value) use ($query) {
            $query->where('inspect_date', '<=', $value);
        });

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
        $payload = $request->validate([
            'pn' => 'required|string',
            'inspect_date' => 'required|date',
            'shift' => 'required|integer|between:1,4',
            'total_units' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($inspection) {
                    $totalInspected = $inspection->total_approved + $inspection->total_rejected;
                    $remainingUnits = $inspection->inventory - $totalInspected;
                    if ($value > $remainingUnits) {
                        $fail("Solo quedan {$remainingUnits} unidades por inspeccionar");
                    }
                }
            ],
            'comment' => 'nullable|string',
            'attributes' => 'required|array',
            'attributes.*.custom_attribute_id' => 'required|exists:custom_attributes,id',
            'attributes.*.value' => 'required|string',
        ]);

        return DB::transaction(function () use ($payload, $inspection) {
            $lot = $inspection->lots()->create([
                'total_rejects' => 0,
                'total_reworks' => 0,
            ] + $payload);
            $lot->attributes()->sync($payload['attributes']);
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
        $payload = $request->validate([
            'pn' => 'required|string',
            'inspect_date' => 'required|date',
            'shift' => 'required|integer|between:1,4',
            'total_units' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($inspectionLot) {
                    $inspection = $inspectionLot->inspection;
                    $totalInspected = $inspection->total_approved + $inspection->total_rejected;
                    $remainingUnits = $inspection->inventory - $totalInspected + $inspectionLot->total_units;
                    if ($value > $remainingUnits) {
                        $fail("Solo quedan {$remainingUnits} unidades por inspeccionar");
                    }
                }
            ],
            'comment' => 'nullable|string',
            'attributes' => 'required|array',
            'attributes.*.custom_attribute_id' => 'required|exists:custom_attributes,id',
            'attributes.*.value' => 'required|string',
        ]);
        return DB::transaction(function () use ($payload, $inspectionLot) {
            $inspectionLot->update($payload);
            $inspectionLot->attributes()->sync(
                collect($payload['attributes'])
                    ->mapWithKeys(fn($attribute) => [$attribute['custom_attribute_id'] => ['value' => $attribute['value']]])
            );

            return $inspectionLot;
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InspectionLot $inspectionLot)
    {
        //
    }
}
