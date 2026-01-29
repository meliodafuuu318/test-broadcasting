<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        $messages = Message::latest()->take(50)->get()->reverse()->values();
        return view('chat', compact('messages'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'user_name' => $request->user_name,
            'message' => $request->message,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function getMessages()
    {
        $messages = Message::latest()->take(50)->get()->reverse()->values();
        return response()->json($messages);
    }
}