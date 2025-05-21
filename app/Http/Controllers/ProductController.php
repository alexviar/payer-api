<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
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

    public function show(Product $product)
    {
        $product->load(['client', 'attributes', 'lastInspection']);
        $product->loadCount('inspections');
        return $product;
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);
        $payload = $this->preparePayload($request, $product);

        $product = DB::transaction(function () use ($payload, $product) {
            $product->update(Arr::except($payload, 'attributes'));
            if (Arr::has($payload, 'attributes')) {
                $product->attributes()->sync($payload['attributes']);
            }
            return $product;
        });

        $product->load(['client', 'attributes']);
        $product->loadCount('inspections');
        return $product;
    }

    protected function preparePayload(Request $request, ?Product $product = null)
    {
        $testMessages = app()->environment('testing') ? [
            'name.required' => 'required',
            'manufacturer.required' => 'required',
            'client_id.required' => 'required',
            'attributes.required' => 'required'
        ] : [];

        return $request->validate([
            'name' => array_merge(['required', 'string'], $product ? ['sometimes'] : []),
            'manufacturer' => array_merge(['required', 'string'], $product ? ['sometimes'] : []),
            'client_id' => array_merge(['required', 'integer', 'exists:clients,id'], $product ? ['sometimes'] : []),
            'attributes' => array_merge(['required', 'array'], $product ? ['sometimes'] : []),
            'attributes.*' => ['integer', 'exists:custom_attributes,id'],
        ], $testMessages);
    }
}
