<?php

namespace App\Http\Controllers;

use App\Models\ReworkInstance;
use App\Models\InspectionLot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReworkInstanceController extends Controller
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
                DB::table($inspectionLot->getTable())->update(['total_reworks' => DB::raw('total_reworks + 1')]);
                return $inspectionLot->reworkInstances()->create($payload);
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
    public function show(ReworkInstance $reworkInstance)
    {
        //
    }

    public function downloadEvidence(ReworkInstance $instance, $evidence)
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
    public function edit(ReworkInstance $reworkInstance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReworkInstance $reworkInstance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReworkInstance $instance)
    {
        $instance->delete();
        $instance->lot()->decrement('total_reworks');
        return response()->noContent();
    }

    protected function preparePayload(Request $request)
    {
        $payload = $request->validate([
            'rework_id' => 'required|exists:reworks,id',
            'evidences' => 'required|array',
            'evidences.*' => 'required|file|image'
        ]);

        $payload['evidences'] = array_map(function ($evidence) {
            return $evidence->store('rework-evidences');
        }, $payload['evidences']);

        return $payload;
    }
}
