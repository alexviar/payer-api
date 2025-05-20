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

    public function store(Request $request)
    {
        $this->authorize('create', [Plant::class, $request->all()]);
        $payload = $this->preparePayload($request);
        return Plant::create($payload);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Plant $plant)
    {
        $this->authorize('view', $plant);

        if ($request->has('with')) {
            $relations = explode(',', $request->input('with'));
            $plant->loadMissing($relations);
        }

        return $plant;
    }

    /**
     * Update the specified plant in storage.
     */
    public function update(Request $request, Plant $plant)
    {
        $this->authorize('update', $plant);

        $payload = $this->preparePayload($request);

        $plant->update($payload);

        return $plant;
    }

    /**
     * Remove the specified plant from storage.
     */
    public function destroy(Plant $plant)
    {
        $this->authorize('delete', $plant);

        abort_if(
            $plant->inspections()->exists(),
            409,
            'No se puede eliminar la planta porque tiene inspecciones relacionadas.'
        );

        $plant->delete();

        return response()->noContent();
    }

    protected function preparePayload(Request $request)
    {
        $payload = $request->validate([
            'name' => ['required', 'string'],
            'address' => ['required', 'string']
        ]);

        return $payload;
    }
}
