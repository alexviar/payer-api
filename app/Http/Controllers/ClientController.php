<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{

    public function applyFilters(Request $request, Builder $query)
    {
        if ($request->has('search')) {
            $query->where('name', 'like', "%$request->search%");
        }
    }

    public function index(Request $request)
    {
        $query = Client::query();

        $query->with(['lastInspection'])
            ->withCount(['products']);

        $this->applyFilters($request, $query);

        /** @var LengthAwarePaginator $result */
        $result = $query->paginate($request->input('page_size'));

        return $result;
    }

    public function store(Request $request)
    {
        $this->authorize('create', Client::class);

        $validated = $this->preparePayload($request);
        $client = Client::create($validated);

        return response()->json($client, 201);
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);
        $client->loadCount('products');
        $client->load('lastInspection');
        return $client;
    }

    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $validated = $this->preparePayload($request, $client);
        $client->update($validated);

        return response()->json($client);
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        abort_if(
            $client->products()->whereHas('inspections')->exists(),
            409,
            'No se puede eliminar el cliente porque tiene inspecciones registradas.'
        );
        DB::transaction(function () use ($client) {
            $client->products()->delete();
            $client->delete();
        });

        return response()->noContent();
    }

    protected function preparePayload(Request $request, ?Client $client = null)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'representative' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        return $validated;
    }
}
