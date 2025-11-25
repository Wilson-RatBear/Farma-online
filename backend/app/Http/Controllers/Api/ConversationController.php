<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    // Obtener conversaciones del usuario
    public function index()
    {
        $user = Auth::user();
        
        $conversations = Conversation::with(['user', 'latestMessage'])
            ->where('user_id', $user->id)
            ->orderBy('last_message_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'conversations' => $conversations
        ]);
    }

    // Crear nueva conversación
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        $user = Auth::user();

        // Crear conversación
        $conversation = Conversation::create([
            'user_id' => $user->id,
            'subject' => $request->subject,
            'last_message_at' => now()
        ]);

        // Crear primer mensaje
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'message' => $request->message
        ]);

        return response()->json([
            'success' => true,
            'conversation' => $conversation->load('user', 'messages')
        ]);
    }

    // Obtener mensajes de una conversación
    public function show($id)
    {
        $conversation = Conversation::with(['messages.user'])
            ->where('id', $id)
            ->firstOrFail();

        // Marcar mensajes como leídos
        Message::where('conversation_id', $id)
            ->where('user_id', '!=', Auth::id())
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'conversation' => $conversation
        ]);
    }
}