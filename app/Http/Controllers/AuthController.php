<?php

namespace App\Http\Controllers;

use App\Models\Pin;
use App\Models\User;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|confirmed|min:4',
            'title'       => 'required|string|max:255',
            'pin'         => 'required|string|min:4|max:10',
        ]);

        try {
            $user = null;
            $album = null;

            DB::transaction(function () use ($request, &$user, &$album) {

                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                $album = Album::createWithQr([
                    'title'   => $request->title,
                    'user_id' => $user->id,
                ]);

                Pin::create([
                    'pin'      => $request->pin,
                    'album_id' => $album->id,
                ]);
            });
            auth()->login($user);

            return response()->json([
                'success' => true,
                'message' => 'Registrierung erfolgreich!',
                'user'    => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
                'album' => [
                    'id'      => $album->id,
                    'title'   => $album->title,
                    'qr_code' => $album->qr_code,
                ],
            ], 201);

        } catch (Throwable $e) {
            Log::error('Registration failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Fehler bei der Registrierung',
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ungültige Zugangsdaten',
                ], 401);
            }

            auth()->login($user);

            return response()->json([
                'success' => true,
                'message' => 'Login erfolgreich',
                'user'    => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
            ]);

        } catch (Throwable $e) {
            Log::error('Login failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Fehler beim Login',
            ], 500);
        }
    }
}
