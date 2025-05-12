<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PlantController extends Controller
{
    public function applyFilters(Request $request, Builder $query)
    {
        if ($request->has('search')) {
            $query->where('name', 'like', "%$request->search%");
        }
    }

    public function index(Request $request)
    {
        $query = Plant::query();

        $this->applyFilters($request, $query);

        /** @var LengthAwarePaginator $result */
        $result = $query->paginate($request->input('page_size'));

        return $result;
    }
}
