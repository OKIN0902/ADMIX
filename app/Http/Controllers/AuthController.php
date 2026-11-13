<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('users', 'name')],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => trim($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => 'murid',
            'gender' => null,
        ]);

        // Registrasi tidak membuat sesi. Pengguna login sendiri setelah popup sukses.
        return response()->json([
            'message' => 'Registrasi berhasil',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $identity = trim($validated['login']);
        $query = filter_var($identity, FILTER_VALIDATE_EMAIL)
            ? User::where('email', $identity)
            : User::where('name', $identity);

        // Nama lama mungkin tidak unik; jangan memilih satu akun secara acak.
        $matches = $query->limit(2)->get();
        $user = $matches->count() === 1 ? $matches->first() : null;

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Nama lengkap/email atau password salah.',
            ], 401);
        }

        $token = $user->createToken('admix')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gender' => ['required', Rule::in(['male', 'female'])],
        ]);

        $user = $request->user();

        // Gender dipilih sekali saat pertama kali. Perubahan selanjutnya
        // membutuhkan alur pengubahan profil tersendiri.
        if ($user->gender !== null && $user->gender !== '') {
            return response()->json([
                'message' => 'Gender akun sudah tersimpan.',
                'user' => $user,
            ], 409);
        }

        $user->update([
            'gender' => $validated['gender'],
        ]);

        return response()->json([
            'message' => 'Gender berhasil disimpan',
            'user' => $user->fresh(),
        ], 200);
    }
}
