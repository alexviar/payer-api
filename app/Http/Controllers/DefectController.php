<?php

namespace App\Http\Controllers;

use App\Models\Defect;
use Illuminate\Http\Request;

class DefectController extends Controller
{
    public function index(Request $request)
    {
        $query = Defect::query();

        /** @var LengthAwarePaginator $result */
        $result = $query->paginate($request->input('page_size'));

        return $result;
    }
}
