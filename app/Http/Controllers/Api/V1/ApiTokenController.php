<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ApiTokenController extends Controller
{
    private const ABILITIES = ['content:read', 'profile:read', 'mobile:read', 'mobile:write'];

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'], 'password' => ['required', 'string'],
            'name' => ['required', 'string', 'max:120'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => ['string', Rule::in(self::ABILITIES)],
            'expires_in_days' => ['nullable', 'integer', 'between:1,365'],
        ]);
        $user = User::active()->where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Credenciais inválidas.'], 422);
        }

        $plainTextToken = 'lzc_'.Str::random(64);
        $token = ApiToken::create([
            'user_id' => $user->id, 'name' => $data['name'],
            'token_hash' => hash('sha256', $plainTextToken),
            'abilities' => array_values(array_unique($data['abilities'])),
            'expires_at' => now()->addDays($data['expires_in_days'] ?? 30),
        ]);

        return response()->json([
            'token' => $plainTextToken, 'token_type' => 'Bearer',
            'abilities' => $token->abilities, 'expires_at' => $token->expires_at->toIso8601String(),
        ], 201);
    }

    public function show(Request $request): JsonResponse
    {
        /** @var ApiToken $token */
        $token = $request->attributes->get('api_token');

        return response()->json(['data' => [
            'id' => $request->user()->id, 'name' => $request->user()->name,
            'email' => $request->user()->email, 'token_name' => $token->name,
            'abilities' => $token->abilities,
        ]]);
    }

    public function destroy(Request $request): JsonResponse
    {
        /** @var ApiToken $token */
        $token = $request->attributes->get('api_token');
        $token->update(['revoked_at' => now()]);

        return response()->json(['message' => 'Token revogado.']);
    }
}
