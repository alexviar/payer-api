<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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
        $this->authorize('create', Product::class);
        $payload = $this->preparePayload($request);

        $product = DB::transaction(function () use ($payload) {
            $product = Product::create($payload);
            $product->attributes()->sync($payload['attributes']);
            return $product;
        });

        $product->load(['client', 'attributes']);
        $product->loadCount('inspections');
        return $product;
    }

    protected function preparePayload(Request $request)
    {
        return $request->validate([
            'name' => ['required', 'string'],
            'manufacturer' => ['required', 'string'],
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'attributes' => ['required', 'array'],
            'attributes.*' => ['integer', 'exists:custom_attributes,id'],
        ]);
    }
}
