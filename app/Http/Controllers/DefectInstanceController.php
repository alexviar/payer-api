<?php

namespace App\Http\Controllers;

use App\Models\DefectInstance;
use App\Models\InspectionLot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DefectInstanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(Request $request, InspectionLot $inspectionLot)
    {
        $payload = $this->preparePayload($request);
        try {
            return DB::transaction(function () use ($payload, $inspectionLot) {
                DB::table($inspectionLot->getTable())->update(['total_rejects' => DB::raw('total_rejects + 1')]);
                return $inspectionLot->defectInstances()->create($payload);
            });
        } catch (\Throwable $th) {
            foreach ($payload['evidences'] as $evidence) {
                Storage::delete($evidence);
            }
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DefectInstance $defectInstance)
    {
        //
    }

    public function downloadEvidence(DefectInstance $instance, $evidence)
    {
        $evidencePath = collect($instance->evidences)
            ->first(function ($path) use ($evidence) {
                return basename($path) === $evidence;
            });

        if (!$evidencePath || !Storage::exists($evidencePath)) {
            abort(404, 'Evidence file not found');
        }

        return Storage::download(
            $evidencePath,
            null,
            [
                'Content-Disposition' => 'attachment',
                'Cache-Control' => 'private, must-revalidate, max-age=' . (60 * 30) // 30 minutes cache
            ]
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DefectInstance $defectInstance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DefectInstance $defectInstance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DefectInstance $instance)
    {
        DB::transaction(function () use ($instance) {
            $instance->delete();
            $instance->lot()->decrement('total_rejects');
            foreach ($instance->evidences as $evidence) {
                Storage::delete($evidence);
            }
        });
        return response()->noContent();
    }

    protected function preparePayload(Request $request)
    {
        $payload = $request->validate([
            'defect_id' => 'required|exists:defects,id',
            'evidences' => 'required|array',
            'evidences.*' => 'required|file|image'
        ]);

        $payload['evidences'] = array_map(function ($evidence) {
            return $evidence->store('evidences');
        }, $payload['evidences']);

        return $payload;
    }
}
