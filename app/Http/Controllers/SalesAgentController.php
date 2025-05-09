<?php

namespace App\Http\Controllers;

use App\Models\SalesAgent;
use Illuminate\Http\Request;

class SalesAgentController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesAgent::query();

        /** @var LengthAwarePaginator $result */
        $result = $query->paginate($request->input('page_size'));

        return $result;
    }
}
