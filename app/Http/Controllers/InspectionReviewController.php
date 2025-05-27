<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\InspectionReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InspectionReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $inspection)
    {
        $query = InspectionReview::query();

        $query->where('inspection_id', $inspection);

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
            $inspection->update([
                'status' => $request->input('review_outcome') == InspectionReview::APPROVED
                    ? Inspection::COMPLETED_STATUS
                    : Inspection::ACTIVE_STATUS
            ]);
            return $inspection->reviews()->create([
                'review_date' => now(),
                'reviewer_id' => $request->user()->id,
            ] + $request->all());
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(InspectionReview $inspectionReview)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InspectionReview $inspectionReview)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InspectionReview $inspectionReview)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InspectionReview $inspectionReview)
    {
        //
    }
}
