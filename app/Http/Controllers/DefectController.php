<?php

namespace App\Http\Controllers;

use App\Models\Defect;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DefectController extends Controller
{

    public function applyFilters(Request $request, Builder $query)
    {
        if ($request->has('search')) {
            $query->where('name', 'like', "%$request->search%");
        }
    }

    public function index(Request $request)
    {
        $query = Defect::query();

        $this->applyFilters($request, $query);

        /** @var LengthAwarePaginator $result */
        $result = $query->paginate($request->input('page_size'));

        return $result;
    }

    public function store(Request $request)
    {
        return Defect::create($request->all());
    }
}
