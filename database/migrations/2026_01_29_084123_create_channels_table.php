<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['public', 'private', 'direct']); // public, private group, or 1-to-1 direct
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('channel_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('joined_at')->useCurrent();
            $table->unique(['channel_id', 'user_id']);
            $table->timestamps();
        });

        // Update messages table to reference channels
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('channel_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['channel_id']);
            $table->dropColumn('channel_id');
        });
        
        Schema::dropIfExists('channel_user');
        Schema::dropIfExists('channels');
    }
};