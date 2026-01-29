<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class)->withTimestamps();
    }

    // Get all channels user can access (owned channels + public channels)
    public function accessibleChannels()
    {
        return Channel::where('type', Channel::TYPE_PUBLIC)
            ->orWhereHas('users', function ($query) {
                $query->where('user_id', $this->id);
            })
            ->with('users')
            ->get();
    }

    // Get direct message channels
    public function directChannels()
    {
        return $this->channels()->where('type', Channel::TYPE_DIRECT)->get();
    }

    // Get private group channels
    public function privateChannels()
    {
        return $this->channels()->where('type', Channel::TYPE_PRIVATE)->get();
    }
}