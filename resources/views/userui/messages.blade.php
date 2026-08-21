<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EventIntel - Messages</title>
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/userui/navbar.css') }}">
    <style>
        :root { --panel: #fff; --border: #e3e6e8; --muted: #707980; --text: #242a2f; --shadow: 0 12px 28px rgba(52,62,70,.12); }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { min-height: 100vh; color: var(--text); background: #fff; }
        .container { width: 100%; min-height: 100vh; padding: 6px 48px 40px; }
        .messages-page { display: flex; flex-direction: column; gap: 24px; }
        .messages-heading h1 { margin-bottom: 6px; color: #242a2f; font-size: 32px; }
        .messages-heading p { color: var(--muted); }
        .chat-container { display: grid; grid-template-columns: 300px minmax(0, 1fr); gap: 20px; }
        .chat-sidebar, .chat-box { border: 1px solid var(--border); border-radius: 24px; background: var(--panel); box-shadow: var(--shadow); }
        .chat-sidebar { max-height: 70vh; overflow-y: auto; padding: 16px; }
        .chat-sidebar h2 { margin-bottom: 14px; font-size: 22px; }
        .search { margin-bottom: 15px; }
        .search input { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 10px; color: var(--text); background: #fafafa; }
        .chat-item { display: block; position: relative; margin-bottom: 10px; padding: 12px; border: 1px solid transparent; border-radius: 16px; color: inherit; text-decoration: none; background: #fff; }
        .chat-item:hover, .chat-item.active { border-color: rgba(243,197,71,.3); background: rgba(243,197,71,.1); }
        .chat-info h3 { margin-bottom: 4px; font-size: 14px; }
        .chat-info p { color: var(--muted); font-size: 12px; }
        .role-badge { display: inline-block; margin-top: 4px; padding: 3px 8px; border-radius: 999px; color: #b07c00; background: rgba(243,197,71,.15); font-size: 10px; font-weight: 700; }
        .chat-box { display: flex; flex-direction: column; height: 70vh; min-height: 560px; overflow: hidden; }
        .chat-header { padding: 18px; border-bottom: 1px solid var(--border); }
        .chat-header h2 { margin-bottom: 5px; font-size: 24px; }
        .chat-header small { color: var(--muted); }
        .chat-messages { flex: 1; min-height: 0; overflow-y: auto; padding: 18px; }
        .chat-msg { display: block; width: fit-content; max-width: 78%; min-width: 120px; margin: 0 auto 12px 0; padding: 12px 14px; border-radius: 16px; text-align: left; white-space: pre-line; overflow-wrap: anywhere; }
        .chat-msg.sent { margin-right: 0; margin-left: auto; text-align: right; background: rgba(243,197,71,.2); }
        .chat-msg.received { background: #f5f5f5; }
        .chat-msg .meta { display: block; margin-top: 6px; color: var(--muted); font-size: 11px; }
        .chat-footer { display: flex; gap: 10px; margin-top: auto; padding: 16px; border-top: 1px solid var(--border); }
        .chat-footer textarea { flex: 1; min-height: 80px; padding: 14px; border: 1px solid var(--border); border-radius: 16px; resize: vertical; color: var(--text); background: #fff; }
        .chat-footer button { align-self: flex-end; padding: 14px 18px; border: 0; border-radius: 16px; color: #111; background: #f3c547; cursor: pointer; font-weight: 700; }
        .message-alert { margin: 16px 18px 0; padding: 12px 16px; border-radius: 16px; color: #b00; background: rgba(255,77,77,.12); }
        .empty-state { padding: 8px 0; color: var(--muted); line-height: 1.5; }
        @media (max-width: 900px) { .container { padding: 6px 20px 30px; } .chat-container { grid-template-columns: 1fr; } .chat-sidebar { max-height: none; } .chat-box { height: 70vh; min-height: 480px; } }
        @media (max-width: 520px) { .chat-footer { flex-direction: column; } .chat-footer button { align-self: stretch; } }
    </style>
</head>
<body>
    <div class="container messages-page">
        @include('userui.partials.navbar', ['active' => ''])

        <header class="messages-heading">
            <h1>Messages</h1>
            <p>Stay connected with your event coordinator and service providers.</p>
        </header>

        <main class="chat-container">
            <aside class="chat-sidebar">
                <h2>Conversations</h2>
                <div class="search"><input type="search" placeholder="Search chats..." aria-label="Search chats" readonly></div>
                @forelse($threads as $thread)
                    @php($active = $selectedThread && $selectedThread['event_id'] === $thread['event_id'] && $selectedThread['user_id'] === $thread['user_id'])
                    <a class="chat-item {{ $active ? 'active' : '' }}" href="{{ route('your.messages', ['event_id' => $thread['event_id'], 'user_id' => $thread['user_id']]) }}">
                        <div class="chat-info">
                            <h3>{{ $thread['name'] }}</h3>
                            <p>{{ $thread['event_title'] }} &bull; {{ $thread['event_date'] ?: 'No date' }}</p>
                            <span class="role-badge">{{ $thread['role'] }}</span>
                        </div>
                    </a>
                @empty
                    <p class="empty-state">No conversations yet. Once you book suppliers or a coordinator, you can chat with them here.</p>
                @endforelse
            </aside>

            <section class="chat-box">
                @if(!$selectedThread)
                    <div class="chat-header"><h2>Select a conversation</h2></div>
                @else
                    <div class="chat-header">
                        <h2>{{ $selectedThread['name'] }} <span class="role-badge">{{ $selectedThread['role'] }}</span></h2>
                        <small>{{ $selectedThread['role'] === 'Coordinator' ? 'Event coordination' : 'Service provider' }} &bull; {{ $selectedThread['event_title'] }}</small>
                    </div>
                    @if(session('message_error'))<div class="message-alert">{{ session('message_error') }}</div>@endif
                    @error('message')<div class="message-alert">{{ $message }}</div>@enderror
                    <div class="chat-messages" id="chatMessages">
                        @forelse($messages as $message)
                            @php($sent = (int) ($message['sender_id'] ?? 0) === (int) auth()->id())
                            <div class="chat-msg {{ $sent ? 'sent' : 'received' }}" data-mid="{{ $message['message_id'] ?? $message['timestamp'] ?? 0 }}">
                                <strong>{{ $message['sender_name'] ?? ($sent ? 'You' : $selectedThread['name']) }}</strong><br>
                                {{ $message['message'] ?? $message['body'] ?? '' }}
                                <span class="meta">{{ $message['created_at'] ?? '' }}</span>
                            </div>
                        @empty
                            <div class="chat-msg received" id="messageStatus">No messages yet. Start the conversation.</div>
                        @endforelse
                    </div>
                    <form class="chat-footer" method="POST" action="{{ route('your.messages.send') }}">
                        @csrf
                        <input type="hidden" name="event_id" value="{{ $selectedEventId }}">
                        <input type="hidden" name="recipient_id" value="{{ $selectedUserId }}">
                        <textarea name="message" placeholder="Type your message..." required>{{ old('message') }}</textarea>
                        <button type="submit"><i class="fas fa-paper-plane" aria-hidden="true"></i> Send</button>
                    </form>
                @endif
            </section>
        </main>
    </div>

    @if($selectedThread)
        <script>
            (() => {
                const messagesBox = document.getElementById('chatMessages');
                let lastId = Number(@json($lastMessageId));
                const apiUrl = @json(route('your.messages.api'));
                const eventId = @json($selectedEventId);
                const otherUserId = @json($selectedUserId);
                const currentUserId = @json((int) auth()->id());
                const otherName = @json($selectedThread['name']);

                function appendMessages(messages) {
                    messages.forEach(message => {
                        const messageId = Number(message.message_id || message.timestamp || 0);
                        if (messageId <= lastId || messagesBox.querySelector(`[data-mid="${messageId}"]`)) return;
                        lastId = messageId;
                        const sent = Number(message.sender_id) === currentUserId;
                        const item = document.createElement('div');
                        item.className = `chat-msg ${sent ? 'sent' : 'received'}`;
                        item.dataset.mid = messageId;
                        const sender = document.createElement('strong');
                        sender.textContent = message.sender_name || (sent ? 'You' : otherName);
                        const meta = document.createElement('span');
                        meta.className = 'meta';
                        meta.textContent = message.created_at || (message.timestamp ? new Date(Number(message.timestamp) * 1000).toLocaleString() : '');
                        item.append(sender, document.createElement('br'), document.createTextNode(message.message || message.body || ''), meta);
                        messagesBox.appendChild(item);
                    });
                    const status = document.getElementById('messageStatus');
                    if (status && messages.length) status.remove();
                    messagesBox.scrollTop = messagesBox.scrollHeight;
                }

                async function poll() {
                    try {
                        const response = await fetch(`${apiUrl}?action=poll&event_id=${eventId}&other_user_id=${otherUserId}&last_id=${lastId}`, { headers: { Accept: 'application/json' } });
                        const data = await response.json();
                        if (Array.isArray(data.messages)) appendMessages(data.messages);
                    } catch (error) {
                        console.error('Message polling failed', error);
                    }
                }

                messagesBox.scrollTop = messagesBox.scrollHeight;
                poll();
                window.setInterval(poll, 5000);
            })();
        </script>
    @endif
</body>
</html>
