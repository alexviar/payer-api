<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\Unique;

class UserController extends Controller
{
    public function applyFilters(Request $request, Builder $query)
    {
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%$request->search%")
                    ->orWhere('email', 'like', "%$request->search%");
            });
        }

        if ($request->has('filter.role')) {
            $query->where('role', $request->input('filter.role'));
        }
    }

    public function index(Request $request)
    {
        $query = User::query();

        $this->applyFilters($request, $query);

        /** @var LengthAwarePaginator $result */
        $result = $query->paginate($request->input('page_size'));

        return $result;
    }

    public function store(Request $request)
    {
        $this->authorize('create', [User::class, $request->all()]);
        $payload = $this->preparePayload($request);

        // Encriptar la contraseña
        $payload['password'] = Hash::make($payload['password']);

        $user = User::create($payload);
        $user->settings()->create([
            'language' => 'es',
            'notifications_enabled' => true
        ]);

        return $user;
    }

    public function show(User $user)
    {
        return $user;
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', [$user, $request->all()]);
        $payload = $this->preparePayload($request, $user);

        // Si se proporciona una nueva contraseña, encriptarla
        if (isset($payload['password'])) {
            $payload['password'] = Hash::make($payload['password']);
        }

        $user->update($payload);

        return $user;
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $isLastSuperadmin = $user->role === User::SUPERADMIN_ROLE && User::where('role', User::SUPERADMIN_ROLE)->count() <= 1;
        logger('Last Superadmin', [$isLastSuperadmin, User::where('role', User::SUPERADMIN_ROLE)->get()->toArray()]);

        abort_if($isLastSuperadmin, 409, 'No se puede eliminar el último superadministrador del sistema.');

        $user->forceDelete();
        return response()->noContent();
    }

    protected function preparePayload(Request $request, ?User $user = null)
    {
        $testMessages = app()->environment('testing') ? [
            'name.required' => 'required',
            'email.required' => 'required',
            'phone.required' => 'required',
            'role.required' => 'required',
            'password.required' => 'required'
        ] : [];

        $rules = [
            'name' => array_merge($user ? ['sometimes'] : [], ['required', 'string', 'max:255']),
            'email' => array_merge($user ? ['sometimes'] : [], ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user?->id)]),
            'phone' => array_merge($user ? ['sometimes'] : [], ['required', 'string', 'max:16']),
            'role' => array_merge($user ? ['sometimes'] : [], ['required', 'in:' . implode(',', [User::SUPERADMIN_ROLE, User::ADMIN_ROLE, User::GROUP_LEADER_ROLE])]),
            'password' => array_merge($user ? ['sometimes'] : [], ['required', Password::default()])
        ];

        return $request->validate($rules, $testMessages);
    }
}
