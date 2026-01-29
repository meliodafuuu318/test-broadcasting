<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Channel;
use App\Models\User;
use App\Events\MessageSent;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get all channels the user can access
        $channels = $user->accessibleChannels();
        
        // Get the active channel (default to first public or user's first channel)
        $activeChannel = $channels->first();
        
        $messages = $activeChannel 
            ? Message::where('channel_id', $activeChannel->id)
                ->with('user')
                ->latest()
                ->take(50)
                ->get()
                ->reverse()
                ->values()
            : collect();
        
        // Get all users for direct messaging
        $users = User::where('id', '!=', $user->id)->get();
        
        return view('chat', compact('channels', 'activeChannel', 'messages', 'users'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'channel_id' => 'required|exists:channels,id',
        ]);

        $channel = Channel::findOrFail($request->channel_id);
        
        // Check if user has access to this channel
        if (!$channel->isPublic() && !$channel->hasUser(auth()->user())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message = Message::create([
            'user_id' => auth()->id(),
            'channel_id' => $request->channel_id,
            'message' => $request->message,
        ]);

        $message->load('user', 'channel');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function getMessages(Request $request, $channelId)
    {
        $channel = Channel::findOrFail($channelId);
        
        // Check access
        if (!$channel->isPublic() && !$channel->hasUser(auth()->user())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = Message::where('channel_id', $channelId)
            ->with('user')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();
            
        return response()->json($messages);
    }

    public function createChannel(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:public,private',
            'description' => 'nullable|string|max:1000',
            'user_ids' => 'array', // For private channels
            'user_ids.*' => 'exists:users,id',
        ]);

        $channel = Channel::create([
            'name' => $request->name,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        // Add creator to the channel
        $channel->addUser(auth()->user());

        // Add other users for private channels
        if ($request->type === 'private' && $request->user_ids) {
            foreach ($request->user_ids as $userId) {
                $user = User::find($userId);
                if ($user) {
                    $channel->addUser($user);
                }
            }
        }

        return response()->json([
            'success' => true,
            'channel' => $channel->load('users'),
        ]);
    }

    public function getOrCreateDirectChannel(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $otherUser = User::findOrFail($request->user_id);
        $channel = Channel::getOrCreateDirectChannel(auth()->user(), $otherUser);

        $messages = Message::where('channel_id', $channel->id)
            ->with('user')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'success' => true,
            'channel' => $channel->load('users'),
            'messages' => $messages,
        ]);
    }

    public function joinChannel(Request $request, $channelId)
    {
        $channel = Channel::findOrFail($channelId);

        // Only public channels can be joined freely
        if (!$channel->isPublic()) {
            return response()->json(['error' => 'Cannot join private channel'], 403);
        }

        $channel->addUser(auth()->user());

        return response()->json([
            'success' => true,
            'channel' => $channel,
        ]);
    }

    public function leaveChannel(Request $request, $channelId)
    {
        $channel = Channel::findOrFail($channelId);
        $channel->removeUser(auth()->user());

        return response()->json([
            'success' => true,
        ]);
    }
}