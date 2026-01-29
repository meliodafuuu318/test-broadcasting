<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ChatController::class, 'index'])->name('chat');
Route::post('/send-message', [ChatController::class, 'sendMessage'])->name('send.message');
Route::get('/messages', [ChatController::class, 'getMessages'])->name('get.messages');