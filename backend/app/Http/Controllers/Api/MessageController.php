<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // Enviar nuevo mensaje
    public function store(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'required|string'
        ]);

        $user = Auth::user();

        // Verificar que el usuario pertenece a la conversación o es admin
        $conversation = Conversation::findOrFail($request->conversation_id);
        
        if ($conversation->user_id != $user->id && !$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para enviar mensajes en esta conversación'
            ], 403);
        }

        // Crear mensaje
        $message = Message::create([
            'conversation_id' => $request->conversation_id,
            'user_id' => $user->id,
            'message' => $request->message
        ]);

        // Actualizar última fecha de mensaje
        $conversation->update([
            'last_message_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => $message->load('user')
        ]);
    }

    // Obtener mensajes de una conversación (para WebSocket updates)
    public function getMessages($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        $user = Auth::user();

        // Verificar permisos
        if ($conversation->user_id != $user->id && !$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $messages = Message::with('user')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }
}  