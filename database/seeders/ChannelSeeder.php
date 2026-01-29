<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Channel;
use Illuminate\Database\Seeder;

class ChannelSeeder extends Seeder
{
    public function run(): void
    {
        // Create a general public channel
        $general = Channel::create([
            'name' => 'general',
            'type' => Channel::TYPE_PUBLIC,
            'description' => 'General discussion for everyone',
        ]);

        // Create a random public channel
        $random = Channel::create([
            'name' => 'random',
            'type' => Channel::TYPE_PUBLIC,
            'description' => 'Off-topic conversations',
        ]);

        // Create an announcements channel
        $announcements = Channel::create([
            'name' => 'announcements',
            'type' => Channel::TYPE_PUBLIC,
            'description' => 'Important announcements',
        ]);

        // Get all users
        $users = User::all();

        // Create a sample private channel if we have enough users
        if ($users->count() >= 2) {
            $privateChannel = Channel::create([
                'name' => 'Team Discussion',
                'type' => Channel::TYPE_PRIVATE,
                'description' => 'Private team discussions',
            ]);

            // Add first two users to private channel
            $privateChannel->users()->attach($users->take(2)->pluck('id'));
        }

        echo "✅ Channels created successfully!\n";
        echo "   - Public: general, random, announcements\n";
        if ($users->count() >= 2) {
            echo "   - Private: Team Discussion\n";
        }
    }
}