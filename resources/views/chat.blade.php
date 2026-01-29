<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Chat App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">💬 Laravel Chat</h1>
                    <p class="text-blue-100 text-sm">Real-time messaging with Pusher</p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm bg-blue-700 hover:bg-blue-800 px-4 py-2 rounded">
                            Logout
                        </button>
                    </form>
                </div>
            </div>

            <!-- Messages Container -->
            <div id="messages" class="h-96 overflow-y-auto p-6 space-y-4 bg-gray-50">
                @foreach($messages as $msg)
                <div class="message flex items-start space-x-2 {{ $msg->user_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $msg->user_id === auth()->id() ? 'bg-green-500' : 'bg-blue-500' }} flex items-center justify-center text-white font-semibold text-sm">
                        {{ strtoupper(substr($msg->user->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 {{ $msg->user_id === auth()->id() ? 'text-right' : '' }}">
                        <div class="flex items-baseline gap-2 {{ $msg->user_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                            <span class="font-semibold text-gray-800">{{ $msg->user->name }}</span>
                            <span class="text-xs text-gray-500">{{ $msg->created_at->format('g:i A') }}</span>
                        </div>
                        <div class="inline-block mt-1">
                            <p class="px-4 py-2 rounded-lg {{ $msg->user_id === auth()->id() ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800' }} break-words">
                                {{ $msg->message }}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Message Input -->
            <div class="p-6 bg-white border-t">
                <form id="message-form" class="flex gap-2">
                    <input 
                        type="text" 
                        id="message-input" 
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Type your message..."
                        maxlength="1000"
                        required
                    >
                    <button 
                        type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:bg-gray-400"
                        id="send-button"
                    >
                        Send
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const currentUserId = {{ auth()->id() }};
        const currentUserName = "{{ auth()->user()->name }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Pusher Configuration
        const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            encrypted: true
        });

        const channel = pusher.subscribe('chat');
        
        channel.bind('message.sent', function(data) {
            addMessage(data.message);
        });

        // Handle form submission
        document.getElementById('message-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const messageInput = document.getElementById('message-input');
            const message = messageInput.value.trim();
            
            if (!message) return;

            const sendButton = document.getElementById('send-button');
            sendButton.disabled = true;

            try {
                const response = await fetch('/send-message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        message: message
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Add our own message to the chat
                    addMessage(data.message);
                    messageInput.value = '';
                }
            } catch (error) {
                console.error('Error sending message:', error);
                alert('Failed to send message. Please try again.');
            } finally {
                sendButton.disabled = false;
                messageInput.focus();
            }
        });

        function addMessage(message) {
            const messagesContainer = document.getElementById('messages');
            
            const messageDiv = document.createElement('div');
            const isOwnMessage = message.user_id === currentUserId;
            messageDiv.className = `message flex items-start space-x-2 ${isOwnMessage ? 'flex-row-reverse' : ''}`;
            
            const initial = message.user.name.charAt(0).toUpperCase();
            const time = new Date(message.created_at).toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit'
            });
            
            const bgColor = isOwnMessage ? 'bg-green-500' : 'bg-blue-500';
            const bubbleColor = isOwnMessage ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800';
            const textAlign = isOwnMessage ? 'text-right' : '';
            const flexDirection = isOwnMessage ? 'flex-row-reverse' : '';
            
            messageDiv.innerHTML = `
                <div class="flex-shrink-0 w-8 h-8 rounded-full ${bgColor} flex items-center justify-center text-white font-semibold text-sm">
                    ${initial}
                </div>
                <div class="flex-1 ${textAlign}">
                    <div class="flex items-baseline gap-2 ${flexDirection}">
                        <span class="font-semibold text-gray-800">${escapeHtml(message.user.name)}</span>
                        <span class="text-xs text-gray-500">${time}</span>
                    </div>
                    <div class="inline-block mt-1">
                        <p class="px-4 py-2 rounded-lg ${bubbleColor} break-words">${escapeHtml(message.message)}</p>
                    </div>
                </div>
            `;
            
            messagesContainer.appendChild(messageDiv);
            scrollToBottom();
        }

        function scrollToBottom() {
            const messagesContainer = document.getElementById('messages');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Scroll to bottom on page load
        scrollToBottom();
    </script>   
</body>
</html>