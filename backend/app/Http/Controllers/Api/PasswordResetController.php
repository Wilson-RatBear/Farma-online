<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    // Solicitar reset de contraseña
    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            // Generar token
            $token = Str::random(60);
            
            // Guardar token en base de datos
            DB::table('password_resets')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now()
                ]
            );

            // Enviar email
            Mail::to($user->email)->send(new PasswordResetMail($user, $token));

            return response()->json([
                'success' => true,
                'message' => 'Se ha enviado un enlace de recuperación a tu email.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error sending reset email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el email de recuperación.'
            ], 500);
        }
    }

    // Resetear contraseña
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Buscar token válido
            $resetRecord = DB::table('password_resets')
                ->where('email', $request->email)
                ->first();

            if (!$resetRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token inválido o expirado.'
                ], 400);
            }

            // Verificar token (1 hora de expiración)
            if (now()->diffInMinutes($resetRecord->created_at) > 60) {
                DB::table('password_resets')->where('email', $request->email)->delete();
                return response()->json([
                    'success' => false,
                    'message' => 'El token ha expirado.'
                ], 400);
            }

            // Verificar token hash
            if (!Hash::check($request->token, $resetRecord->token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token inválido.'
                ], 400);
            }

            // Actualizar contraseña
            $user = User::where('email', $request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            // Eliminar token usado
            DB::table('password_resets')->where('email', $request->email)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contraseña actualizada correctamente. Ahora puedes iniciar sesión.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error resetting password: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al restablecer la contraseña.'
            ], 500);
        }
    }
}