<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Rebuilds the `login`/`logout` actions from the legacy mobile-attendance
 * API (api/rest/api.php). That API had no evidence of any active
 * consumer (see the README for the investigation) and no real
 * authentication — it trusted a client-supplied `user_id` on every call
 * after login, with no token issued at all. This is a fresh design using
 * Sanctum tokens rather than a byte-compatible port, since there was no
 * live client to preserve compatibility for.
 *
 * Reuses LoginRequest — the same MD5-upgrade-shim/rate-limiting logic
 * the web login uses — rather than duplicating it.
 */
class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        // resolveUser() (not authenticate()) — verifies credentials
        // without the side effect of logging this request into the web
        // session guard, which a stateless token API must not do.
        $user = $request->resolveUser();

        if (! $user) {
            throw ValidationException::withMessages(['email' => trans('auth.failed')]);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Logged in successfully.',
            'data' => [
                'user_id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout successfully.',
            'data' => '',
        ]);
    }
}
