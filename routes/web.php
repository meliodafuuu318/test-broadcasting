<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('chat');
});

Route::middleware('auth')->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::post('/send-message', [ChatController::class, 'sendMessage'])->name('send.message');
    Route::get('/messages', [ChatController::class, 'getMessages'])->name('get.messages');
});

require __DIR__.'/auth.php';