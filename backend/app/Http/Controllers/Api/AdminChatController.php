<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminChatController extends Controller
{
    // Obtener todas las conversaciones para admin
    public function index()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $conversations = Conversation::with(['user', 'latestMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'conversations' => $conversations
        ]);
    }

    // Obtener conversaciones no leídas para admin
    public function unreadConversations()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $conversations = Conversation::with(['user', 'latestMessage'])
            ->whereHas('messages', function($query) {
                $query->where('is_read', false)
                      ->where('user_id', '!=', Auth::id());
            })
            ->orderBy('last_message_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'conversations' => $conversations,
            'unread_count' => $conversations->count()
        ]);
    }

    // Cerrar conversación (admin)
    public function closeConversation($id)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $conversation = Conversation::findOrFail($id);
        $conversation->update(['is_closed' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Conversación cerrada'
        ]);
    }

    // Reabrir conversación (admin)
    public function reopenConversation($id)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $conversation = Conversation::findOrFail($id);
        $conversation->update(['is_closed' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Conversación reabierta'
        ]);
    }
}