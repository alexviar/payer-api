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

        /** @var LengthAwarePaginator $result */
        $result = $query->paginate($request->input('page_size'));
        $result->getCollection()->each(fn($inspection) => $inspection->append('client'));

        return $result;
    }

    public function show(Inspection $inspection)
    {
        return $inspection;
    }
}
