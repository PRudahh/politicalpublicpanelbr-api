<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users',
            'password'       => 'required|string|min:8|confirmed',
            'organization' => 'nullable|string|max:255',
        ]);

        $user = \App\Models\User::create([
            ...$validated,
            'password'    => bcrypt($validated['password']),
            'role'        => 'journalist',
            'is_approved' => false,
        ]);

        return response()->json([
            'message' => 'Cadastro realizado. Aguarde aprovação para acessar exportações.',
            'user_id' => $user->id,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciais inválidas.'], 401);
        }

        if (!$user->is_approved) {
            return response()->json(['message' => 'Conta pendente de aprovação.'], 403);
        }

        $token = $user->createToken('journalist-token', ['export', 'compare'])->plainTextToken;

        return response()->json([
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout realizado.']);
    }
}