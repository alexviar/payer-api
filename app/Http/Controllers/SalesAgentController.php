<?php

namespace App\Http\Controllers;

use App\Models\SalesAgent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SalesAgentController extends Controller
{
    public function applyFilters(Request $request, Builder $query)
    {
        if ($request->has('search')) {
            $query->where('name', 'like', "%$request->search%");
        }
    }

    public function index(Request $request)
    {
        $query = SalesAgent::query();

        $this->applyFilters($request, $query);

        /** @var LengthAwarePaginator $result */
        $result = $query->paginate($request->input('page_size'));

        return $result;
    }

    public function show(SalesAgent $salesAgent)
    {
        $this->authorize('view', $salesAgent);
        return $salesAgent;
    }

    public function store(Request $request)
    {
        $this->authorize('create', SalesAgent::class);

        $validated = $this->preparePayload($request);

        $salesAgent = SalesAgent::create($validated);

        return response()->json($salesAgent, 201);
    }

    public function update(Request $request, SalesAgent $salesAgent)
    {
        $this->authorize('update', $salesAgent);

        $validated = $this->preparePayload($request, $salesAgent);

        $salesAgent->update($validated);

        return response()->json($salesAgent);
    }

    public function destroy(SalesAgent $salesAgent)
    {
        $this->authorize('delete', $salesAgent);

        // Verificar si el agente tiene inspecciones relacionadas
        if ($salesAgent->inspections()->exists()) {
            abort(409, 'No se puede eliminar el agente porque tiene inspecciones relacionadas.');
        }

        $salesAgent->delete();

        return response()->noContent();
    }

    protected function preparePayload(Request $request, ?SalesAgent $salesAgent = null)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:sales_agents,email,' . $salesAgent?->id,
            'phone' => 'required|string|max:20',
        ]);
    }
}
