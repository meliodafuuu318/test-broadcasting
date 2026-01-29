<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat App</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-4xl mx-auto px-4 py-4 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Chat Room</h1>
                    <p class="text-sm text-gray-600">Logged in as {{ auth()->user()->name }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Messages Container -->
        <div class="flex-1 max-w-4xl w-full mx-auto p-4">
            <div class="bg-white rounded-lg shadow-lg h-[calc(100vh-250px)] flex flex-col">
                <!-- Messages Area -->
                <div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-4">
                    @foreach($messages as $message)
                        <div class="message flex {{ $message->user_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-xs lg:max-w-md">
                                <div class="flex items-center gap-2 mb-1 {{ $message->user_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                                    <span class="text-sm font-semibold text-gray-700">{{ $message->user->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $message->created_at->format('h:i A') }}</span>
                                </div>
                                <div class="px-4 py-2 rounded-lg {{ $message->user_id === auth()->id() ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900' }}">
                                    {{ $message->message }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Message Input -->
                <div class="border-t p-4">
                    <form id="message-form" class="flex gap-2">
                        @csrf
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
            </div>
        </div>
    </div>

    <script>
        // Auto-scroll to bottom function
        function scrollToBottom() {
            const container = document.getElementById('messages-container');
            container.scrollTop = container.scrollHeight;
        }

        // Initial scroll to bottom
        scrollToBottom();

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const currentUserId = {{ auth()->id() }};
        const currentUserName = "{{ auth()->user()->name }}";

        // Handle form submission
        document.getElementById('message-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const input = document.getElementById('message-input');
            const message = input.value.trim();
            
            if (!message) return;

            try {
                const response = await fetch('{{ route('send.message') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message })
                });

                if (response.ok) {
                    const data = await response.json();
                    // Add our own message to the chat
                    addMessage(data.message, true);
                    input.value = '';
                    scrollToBottom();
                }
            } catch (error) {
                console.error('Error sending message:', error);
                alert('Failed to send message. Please try again.');
            }
        });

        // Function to add a message to the chat
        function addMessage(message, isOwn = false) {
            const container = document.getElementById('messages-container');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message flex ${isOwn ? 'justify-end' : 'justify-start'}`;
            
            const date = new Date(message.created_at);
            const timeString = date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            
            messageDiv.innerHTML = `
                <div class="max-w-xs lg:max-w-md">
                    <div class="flex items-center gap-2 mb-1 ${isOwn ? 'flex-row-reverse' : ''}">
                        <span class="text-sm font-semibold text-gray-700">${message.user.name}</span>
                        <span class="text-xs text-gray-500">${timeString}</span>
                    </div>
                    <div class="px-4 py-2 rounded-lg ${isOwn ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900'}">
                        ${escapeHtml(message.message)}
                    </div>
                </div>
            `;
            
            container.appendChild(messageDiv);
        }

        // HTML escape function to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Listen for new messages via Laravel Echo
        if (window.Echo) {
            window.Echo.channel('chat')
                .listen('.message.sent', (e) => {
                    console.log('New message received:', e);
                    // Only add messages from other users (our own messages are added immediately)
                    if (e.message.user_id !== currentUserId) {
                        addMessage(e.message, false);
                        scrollToBottom();
                    }
                });
        } else {
            console.error('Laravel Echo is not initialized');
        }
    </script>
</body>
</html>