<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials, true) || !Auth::user()->is_active) {
            return response()->json(["message" => __("auth.failed")], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        return response()->json([
            'user' => $user,
            'access_token' => $user->createToken('*')->plainTextToken
        ]);
    }

    public function register(Request $request)
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:16'],
            'password' => ['required', 'confirmed', RulesPassword::default()],
        ]);

        $user = User::create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'],
            'password' => $payload['password'],
            'role' => User::GROUP_LEADER_ROLE,
            'is_active' => true,
        ]);

        return response()->json([
            'user' => $user,
            'access_token' => $user->createToken('*')->plainTextToken
        ], 201);
    }

    public function logout(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var PersonalAccessToken $currentAccessToken */
        $currentAccessToken = $user->currentAccessToken();

        $currentAccessToken->delete();
    }

    public function changePassword(Request $request)
    {
        $payload = $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', RulesPassword::default()],
        ]);

        $request->user()->update([
            'password' => $payload['new_password']
        ]);
    }

    function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)]);
        } else if ($status === Password::RESET_THROTTLED) {
            abort(429, __($status), [
                'Retry-After' => now()->addMinutes(1)->toRfc7231String(),
            ]);
        }
        throw ValidationException::withMessages(['email' => __($status)]);
    }

    function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, $password) {
                $user->forceFill([
                    'password' => $password
                ])->setRememberToken(Str::random(60));

                $user->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['status', __($status)]);
        }
        throw ValidationException::withMessages(['email' => __($status)]);
    }

    public function updateProfile(Request $request)
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
            'phone' => ['required', 'string', 'max:255'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $user->update($payload);

        return response()->json($user);
    }

    public function updateSettings(Request $request)
    {
        $payload = $request->validate([
            'language' => ['sometimes', 'required', 'string', 'in:es,en'],
            'notifications_enabled' => ['sometimes', 'required', 'boolean'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $user->settings()->update($payload);

        return response()->json($user->settings);
    }

    public function deleteAccount(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $user->email = Str::random(10) . '@deleted';
        $user->is_active = false;
        $user->clearPassword();
        $user->save();
        $user->tokens()->delete();
        return response()->noContent();
    }
}
