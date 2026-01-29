<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Channel;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Public channels - anyone authenticated can listen
Broadcast::channel('channel.{channelId}', function ($user, $channelId) {
    $channel = Channel::find($channelId);
    
    if (!$channel) {
        return false;
    }
    
    // If it's a public channel, allow access
    if ($channel->isPublic()) {
        return true;
    }
    
    return false;
});

// Private channels - only members can listen
Broadcast::channel('channel.{channelId}', function ($user, $channelId) {
    $channel = Channel::find($channelId);
    
    if (!$channel) {
        return false;
    }
    
    // Check if user is a member of this channel
    return $channel->hasUser($user);
});

// Legacy public chat channel
Broadcast::channel('chat', function () {
    return true;
});