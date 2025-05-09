<?php

namespace App\Http\Controllers;

use App\Models\Rework;
use Illuminate\Http\Request;

class ReworkController extends Controller
{
    public function index(Request $request)
    {
        $query = Rework::query();

        /** @var LengthAwarePaginator $result */
        $result = $query->paginate($request->input('page_size'));

        return $result;
    }

    public function store(Request $request)
    {
        return Rework::create($request->all());
    }
}
