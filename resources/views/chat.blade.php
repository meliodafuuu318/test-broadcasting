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
            <div class="bg-blue-600 text-white px-6 py-4">
                <h1 class="text-2xl font-bold">💬 Laravel Chat</h1>
                <p class="text-blue-100 text-sm">Real-time messaging with Pusher</p>
            </div>

            <!-- Username Input (if not set) -->
            <div id="username-section" class="p-6 border-b">
                <label class="block text-gray-700 font-semibold mb-2">Enter your name to start chatting:</label>
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        id="username-input" 
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Your name..."
                        maxlength="255"
                    >
                    <button 
                        onclick="setUsername()" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                    >
                        Join Chat
                    </button>
                </div>
            </div>

            <!-- Chat Section (hidden until username is set) -->
            <div id="chat-section" class="hidden">
                <!-- Messages Container -->
                <div id="messages" class="h-96 overflow-y-auto p-6 space-y-4 bg-gray-50">
                    @foreach($messages as $msg)
                    <div class="message flex items-start space-x-2">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold text-sm">
                            {{ strtoupper(substr($msg->user_name, 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-baseline gap-2">
                                <span class="font-semibold text-gray-800">{{ $msg->user_name }}</span>
                                <span class="text-xs text-gray-500">{{ $msg->created_at->format('g:i A') }}</span>
                            </div>
                            <p class="text-gray-700 break-words">{{ $msg->message }}</p>
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
                    <div class="mt-2 text-sm text-gray-600">
                        Chatting as: <span class="font-semibold" id="current-username"></span>
                        <button onclick="changeUsername()" class="text-blue-600 hover:underline ml-2">Change</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let username = localStorage.getItem('chat_username') || '';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Initialize
        if (username) {
            showChatSection();
        }

        function setUsername() {
            const input = document.getElementById('username-input');
            const name = input.value.trim();
            
            if (name) {
                username = name;
                localStorage.setItem('chat_username', username);
                showChatSection();
            }
        }

        function changeUsername() {
            username = '';
            localStorage.removeItem('chat_username');
            document.getElementById('username-section').classList.remove('hidden');
            document.getElementById('chat-section').classList.add('hidden');
            document.getElementById('username-input').value = '';
        }

        function showChatSection() {
            document.getElementById('username-section').classList.add('hidden');
            document.getElementById('chat-section').classList.remove('hidden');
            document.getElementById('current-username').textContent = username;
            document.getElementById('message-input').focus();
            scrollToBottom();
        }

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
                        user_name: username,
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

        // Enter key on username input
        document.getElementById('username-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                setUsername();
            }
        });

        function addMessage(message) {
            const messagesContainer = document.getElementById('messages');
            
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message flex items-start space-x-2';
            
            const initial = message.user_name.charAt(0).toUpperCase();
            const time = new Date(message.created_at).toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit'
            });
            
            messageDiv.innerHTML = `
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold text-sm">
                    ${initial}
                </div>
                <div class="flex-1">
                    <div class="flex items-baseline gap-2">
                        <span class="font-semibold text-gray-800">${escapeHtml(message.user_name)}</span>
                        <span class="text-xs text-gray-500">${time}</span>
                    </div>
                    <p class="text-gray-700 break-words">${escapeHtml(message.message)}</p>
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
    </script>   
</body>
</html>