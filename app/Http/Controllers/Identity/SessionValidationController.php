<?php

namespace App\Http\Controllers\Identity;

use App\Domain\Core\Contracts\MyGrowIdentity;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionValidationController extends Controller
{
    public function __construct(
        private MyGrowIdentity $identity
    ) {}

    public function validate(Request $request): JsonResponse
    {
        $token = $request->bearerToken()
            ?? $request->input('token')
            ?? $request->cookie(config('session.cookie'));

        if (!$token) {
            return response()->json([
                'valid' => false,
                'message' => 'No token or session provided.',
            ], 401);
        }

        $user = $this->identity->validateSession($token);

        if (!$user) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired session.',
            ], 401);
        }

        return response()->json([
            'valid' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}
