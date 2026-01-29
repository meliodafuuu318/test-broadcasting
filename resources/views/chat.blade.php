<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Multi-Channel Chat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-900 text-white flex flex-col">
            <!-- Header -->
            <div class="p-4 border-b border-gray-700">
                <h1 class="text-xl font-bold">Chat App</h1>
                <p class="text-sm text-gray-400">{{ auth()->user()->name }}</p>
            </div>

            <!-- Channels List -->
            <div class="flex-1 overflow-y-auto">
                <!-- Public Channels -->
                <div class="p-4">
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="text-xs font-semibold text-gray-400 uppercase">Public Channels</h2>
                        <button id="create-public-channel-btn" class="text-gray-400 hover:text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                    <div id="public-channels" class="space-y-1">
                        @foreach($channels->where('type', 'public') as $channel)
                            <button 
                                class="channel-btn w-full text-left px-3 py-2 rounded hover:bg-gray-800 {{ $activeChannel && $activeChannel->id === $channel->id ? 'bg-gray-800' : '' }}"
                                data-channel-id="{{ $channel->id }}"
                                data-channel-type="{{ $channel->type }}"
                            >
                                # {{ $channel->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Private Group Channels -->
                <div class="p-4">
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="text-xs font-semibold text-gray-400 uppercase">Private Groups</h2>
                        <button id="create-private-channel-btn" class="text-gray-400 hover:text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                    <div id="private-channels" class="space-y-1">
                        @foreach($channels->where('type', 'private') as $channel)
                            <button 
                                class="channel-btn w-full text-left px-3 py-2 rounded hover:bg-gray-800 {{ $activeChannel && $activeChannel->id === $channel->id ? 'bg-gray-800' : '' }}"
                                data-channel-id="{{ $channel->id }}"
                                data-channel-type="{{ $channel->type }}"
                            >
                                🔒 {{ $channel->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Direct Messages -->
                <div class="p-4">
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="text-xs font-semibold text-gray-400 uppercase">Direct Messages</h2>
                        <button id="create-direct-channel-btn" class="text-gray-400 hover:text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                    <div id="direct-channels" class="space-y-1">
                        @foreach($channels->where('type', 'direct') as $channel)
                            <button 
                                class="channel-btn w-full text-left px-3 py-2 rounded hover:bg-gray-800 {{ $activeChannel && $activeChannel->id === $channel->id ? 'bg-gray-800' : '' }}"
                                data-channel-id="{{ $channel->id }}"
                                data-channel-type="{{ $channel->type }}"
                            >
                                💬 {{ $channel->getDisplayName(auth()->user()) }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Logout -->
            <div class="p-4 border-t border-gray-700">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="flex-1 flex flex-col">
            <!-- Channel Header -->
            <div class="bg-white shadow-sm border-b p-4">
                <h2 id="channel-name" class="text-xl font-bold text-gray-900">
                    @if($activeChannel)
                        {{ $activeChannel->getDisplayName(auth()->user()) }}
                    @else
                        Select a channel
                    @endif
                </h2>
                <p id="channel-description" class="text-sm text-gray-600">
                    @if($activeChannel && $activeChannel->description)
                        {{ $activeChannel->description }}
                    @endif
                </p>
            </div>

            <!-- Messages Container -->
            <div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
                @if($activeChannel)
                    @foreach($messages as $message)
                        <div class="message flex {{ $message->user_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-xs lg:max-w-md">
                                <div class="flex items-center gap-2 mb-1 {{ $message->user_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                                    <span class="text-sm font-semibold text-gray-700">{{ $message->user->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $message->created_at->format('h:i A') }}</span>
                                </div>
                                <div class="px-4 py-2 rounded-lg {{ $message->user_id === auth()->id() ? 'bg-blue-500 text-white' : 'bg-white text-gray-900 shadow' }}">
                                    {{ $message->message }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center text-gray-500">
                        <p>Select a channel to start chatting</p>
                    </div>
                @endif
            </div>

            <!-- Message Input -->
            @if($activeChannel)
                <div class="bg-white border-t p-4">
                    <form id="message-form" class="flex gap-2">
                        @csrf
                        <input type="hidden" id="channel-id" value="{{ $activeChannel->id }}">
                        <input 
                            type="text" 
                            id="message-input"
                            name="message"
                            placeholder="Type your message..." 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                            maxlength="1000"
                        >
                        <button 
                            type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition"
                        >
                            Send
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <!-- Create Public Channel Modal -->
    <div id="create-public-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-xl font-bold mb-4">Create Public Channel</h3>
            <form id="create-public-form">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Channel Name</label>
                    <input type="text" id="public-channel-name" class="w-full px-3 py-2 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="public-channel-description" class="w-full px-3 py-2 border rounded-lg" rows="3"></textarea>
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" id="cancel-public-btn" class="px-4 py-2 border rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Create</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Private Channel Modal -->
    <div id="create-private-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-xl font-bold mb-4">Create Private Group</h3>
            <form id="create-private-form">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Group Name</label>
                    <input type="text" id="private-channel-name" class="w-full px-3 py-2 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="private-channel-description" class="w-full px-3 py-2 border rounded-lg" rows="3"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Add Members</label>
                    <div id="user-checkboxes" class="space-y-2 max-h-40 overflow-y-auto">
                        @foreach($users as $user)
                            <label class="flex items-center">
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="mr-2">
                                {{ $user->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" id="cancel-private-btn" class="px-4 py-2 border rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Create</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Direct Message Modal -->
    <div id="create-direct-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-xl font-bold mb-4">Start Direct Message</h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                <div id="dm-user-list" class="space-y-2 max-h-60 overflow-y-auto">
                    @foreach($users as $user)
                        <button 
                            type="button"
                            class="dm-user-btn w-full text-left px-3 py-2 hover:bg-gray-100 rounded"
                            data-user-id="{{ $user->id }}"
                        >
                            {{ $user->name }}
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="flex justify-end">
                <button type="button" id="cancel-direct-btn" class="px-4 py-2 border rounded-lg">Cancel</button>
            </div>
        </div>
    </div>

    <script type="module">
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const currentUserId = {{ auth()->id() }};
        let currentChannel = {{ $activeChannel ? $activeChannel->id : 'null' }};
        let activeChannels = new Set(); // Track which channels we're listening to

        // Auto-scroll to bottom
        function scrollToBottom() {
            const container = document.getElementById('messages-container');
            container.scrollTop = container.scrollHeight;
        }

        scrollToBottom();

        // Add message to chat
        function addMessage(message, isOwn = false) {
            const container = document.getElementById('messages-container');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message flex ${isOwn ? 'justify-end' : 'justify-start'}`;
            
            const date = new Date(message.created_at);
            const timeString = date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            
            messageDiv.innerHTML = `
                <div class="max-w-xs lg:max-w-md">
                    <div class="flex items-center gap-2 mb-1 ${isOwn ? 'flex-row-reverse' : ''}">
                        <span class="text-sm font-semibold text-gray-700">${escapeHtml(message.user.name)}</span>
                        <span class="text-xs text-gray-500">${timeString}</span>
                    </div>
                    <div class="px-4 py-2 rounded-lg ${isOwn ? 'bg-blue-500 text-white' : 'bg-white text-gray-900 shadow'}">
                        ${escapeHtml(message.message)}
                    </div>
                </div>
            `;
            
            container.appendChild(messageDiv);
            scrollToBottom();
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Send message
        document.getElementById('message-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const input = document.getElementById('message-input');
            const message = input.value.trim();
            const channelId = document.getElementById('channel-id').value;
            
            if (!message || !channelId) return;

            try {
                const response = await fetch('{{ route('send.message') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message, channel_id: channelId })
                });

                if (response.ok) {
                    const data = await response.json();
                    addMessage(data.message, true);
                    input.value = '';
                }
            } catch (error) {
                console.error('Error sending message:', error);
            }
        });

        // Switch channel
        async function switchChannel(channelId, channelType) {
            currentChannel = channelId;
            document.getElementById('channel-id').value = channelId;

            try {
                const response = await fetch(`/channels/${channelId}/messages`);
                const messages = await response.json();
                
                const container = document.getElementById('messages-container');
                container.innerHTML = '';
                
                messages.forEach(msg => {
                    addMessage(msg, msg.user_id === currentUserId);
                });

                // Subscribe to this channel
                subscribeToChannel(channelId, channelType);
            } catch (error) {
                console.error('Error loading messages:', error);
            }
        }

        // Channel switching
        document.querySelectorAll('.channel-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const channelId = btn.dataset.channelId;
                const channelType = btn.dataset.channelType;
                
                // Update active state
                document.querySelectorAll('.channel-btn').forEach(b => b.classList.remove('bg-gray-800'));
                btn.classList.add('bg-gray-800');
                
                switchChannel(channelId, channelType);
            });
        });

        // Setup Echo listener
        function setupEchoListener() {
            if (typeof window.Echo !== 'undefined' && window.Echo) {
                console.log('✅ Echo is ready!');
                
                // Subscribe to current channel if exists
                if (currentChannel) {
                    const channelType = document.querySelector(`[data-channel-id="${currentChannel}"]`)?.dataset.channelType;
                    subscribeToChannel(currentChannel, channelType);
                }
            } else {
                setTimeout(setupEchoListener, 100);
            }
        }

        function subscribeToChannel(channelId, channelType) {
            // Leave previous channels
            activeChannels.forEach(chId => {
                try {
                    window.Echo.leave(`channel.${chId}`);
                    window.Echo.leave(`private-channel.${chId}`);
                } catch (e) {}
            });
            activeChannels.clear();

            // Subscribe to new channel
            const channelName = `channel.${channelId}`;
            const isPrivate = channelType === 'private' || channelType === 'direct';
            
            console.log(`Subscribing to ${isPrivate ? 'private' : 'public'} channel: ${channelName}`);

            const channel = isPrivate 
                ? window.Echo.private(channelName)
                : window.Echo.channel(channelName);

            channel.listen('.message.sent', (e) => {
                console.log('✅ New message:', e);
                if (e.message && e.message.user_id !== currentUserId && e.message.channel_id == currentChannel) {
                    addMessage(e.message, false);
                }
            });

            activeChannels.add(channelId);
        }

        // Modal handlers
        document.getElementById('create-public-channel-btn')?.addEventListener('click', () => {
            document.getElementById('create-public-modal').classList.remove('hidden');
        });

        document.getElementById('cancel-public-btn')?.addEventListener('click', () => {
            document.getElementById('create-public-modal').classList.add('hidden');
        });

        document.getElementById('create-public-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('public-channel-name').value;
            const description = document.getElementById('public-channel-description').value;

            try {
                const response = await fetch('{{ route('create.channel') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ name, description, type: 'public' })
                });

                if (response.ok) {
                    location.reload();
                }
            } catch (error) {
                console.error('Error creating channel:', error);
            }
        });

        document.getElementById('create-private-channel-btn')?.addEventListener('click', () => {
            document.getElementById('create-private-modal').classList.remove('hidden');
        });

        document.getElementById('cancel-private-btn')?.addEventListener('click', () => {
            document.getElementById('create-private-modal').classList.add('hidden');
        });

        document.getElementById('create-private-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('private-channel-name').value;
            const description = document.getElementById('private-channel-description').value;
            const userIds = Array.from(document.querySelectorAll('input[name="user_ids[]"]:checked')).map(cb => cb.value);

            try {
                const response = await fetch('{{ route('create.channel') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ name, description, type: 'private', user_ids: userIds })
                });

                if (response.ok) {
                    location.reload();
                }
            } catch (error) {
                console.error('Error creating channel:', error);
            }
        });

        document.getElementById('create-direct-channel-btn')?.addEventListener('click', () => {
            document.getElementById('create-direct-modal').classList.remove('hidden');
        });

        document.getElementById('cancel-direct-btn')?.addEventListener('click', () => {
            document.getElementById('create-direct-modal').classList.add('hidden');
        });

        document.querySelectorAll('.dm-user-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const userId = btn.dataset.userId;

                try {
                    const response = await fetch('{{ route('direct.channel') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ user_id: userId })
                    });

                    if (response.ok) {
                        location.reload();
                    }
                } catch (error) {
                    console.error('Error creating direct channel:', error);
                }
            });
        });

        setTimeout(setupEchoListener, 100);
    </script>
</body>
</html>