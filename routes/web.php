<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('chat');
});

Route::middleware('auth')->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::post('/send-message', [ChatController::class, 'sendMessage'])->name('send.message');
    Route::get('/channels/{channelId}/messages', [ChatController::class, 'getMessages'])->name('get.messages');
    
    // Channel management
    Route::post('/channels', [ChatController::class, 'createChannel'])->name('create.channel');
    Route::post('/channels/direct', [ChatController::class, 'getOrCreateDirectChannel'])->name('direct.channel');
    Route::post('/channels/{channelId}/join', [ChatController::class, 'joinChannel'])->name('join.channel');
    Route::post('/channels/{channelId}/leave', [ChatController::class, 'leaveChannel'])->name('leave.channel');
});

require __DIR__.'/auth.php';