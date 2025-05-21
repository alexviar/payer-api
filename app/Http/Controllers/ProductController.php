<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductController extends Controller
{
    public function applyFilters(Request $request, Builder $query)
    {
        if ($request->has('search')) {
            $query->where('name', 'like', "%$request->search%");
        }

        if ($request->has('filter.client_id')) {
            $query->where('client_id', $request->input('filter.client_id'));
        }
    }

    public function index(Request $request)
    {
        $query = Product::query();

        $query->with(['client', 'lastInspection', 'attributes']);
        $query->withCount('inspections');

        $this->applyFilters($request, $query);

        /** @var LengthAwarePaginator $result */
        $result = $query->paginate($request->input('page_size'));

        return $result;
    }

    public function store(Request $request)
    {
        $product = Product::create($request->all());
        return $product;
    }
}
