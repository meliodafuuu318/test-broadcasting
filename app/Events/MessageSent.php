<?php

namespace App\Events;

use App\Models\Message;
use App\Models\Channel;
use Illuminate\Broadcasting\Channel as BroadcastChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        $channel = $this->message->channel;

        if (!$channel) {
            // Legacy support for old messages without channels (broadcast to public chat)
            return new BroadcastChannel('chat');
        }

        // Public channels - anyone can listen
        if ($channel->isPublic()) {
            return new BroadcastChannel("channel.{$channel->id}");
        }

        // Private/Direct channels - only members can listen
        return new PrivateChannel("channel.{$channel->id}");
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message->load('user', 'channel'),
        ];
    }
}