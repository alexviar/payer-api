<?php

namespace App\Http\Controllers;

use App\Models\CustomAttribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CustomAttributeController extends Controller
{
    public function applyFilters(Request $request, Builder $query)
    {
        if ($request->has('search')) {
            $query->where('name', 'like', "%$request->search%");
        }
    }

    public function index(Request $request)
    {
        $query = CustomAttribute::query();

        $this->applyFilters($request, $query);

        /** @var LengthAwarePaginator $result */
        $result = $query->paginate($request->input('page_size'));

        return $result;
    }

    public function store(Request $request)
    {
        $attribute = CustomAttribute::create($request->all());
        return $attribute;
    }
}
