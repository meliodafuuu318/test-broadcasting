<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Channel extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Channel types
    const TYPE_PUBLIC = 'public';
    const TYPE_PRIVATE = 'private';
    const TYPE_DIRECT = 'direct';

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function isPublic(): bool
    {
        return $this->type === self::TYPE_PUBLIC;
    }

    public function isPrivate(): bool
    {
        return $this->type === self::TYPE_PRIVATE;
    }

    public function isDirect(): bool
    {
        return $this->type === self::TYPE_DIRECT;
    }

    public function hasUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function addUser(User $user): void
    {
        if (!$this->hasUser($user)) {
            $this->users()->attach($user->id);
        }
    }

    public function removeUser(User $user): void
    {
        $this->users()->detach($user->id);
    }

    // Get or create a direct message channel between two users
    public static function getOrCreateDirectChannel(User $user1, User $user2): Channel
    {
        $userIds = [$user1->id, $user2->id];
        sort($userIds);

        // Try to find existing direct channel between these users
        $channel = Channel::where('type', self::TYPE_DIRECT)
            ->whereHas('users', function ($query) use ($userIds) {
                $query->whereIn('user_id', $userIds);
            }, '=', 2)
            ->first();

        if ($channel) {
            return $channel;
        }

        // Create new direct channel
        $channel = Channel::create([
            'name' => "Direct: {$user1->name} & {$user2->name}",
            'type' => self::TYPE_DIRECT,
        ]);

        $channel->users()->attach($userIds);

        return $channel;
    }

    // Get channel name for display
    public function getDisplayName(User $currentUser): string
    {
        if ($this->isDirect()) {
            // For direct messages, show the other user's name
            $otherUser = $this->users()->where('user_id', '!=', $currentUser->id)->first();
            return $otherUser ? $otherUser->name : 'Direct Message';
        }

        return $this->name;
    }
}