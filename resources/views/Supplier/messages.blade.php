@extends('supplier.layout')

@section('title', 'Messages')
@section('styles')
<style>
    .chat-container { display:grid; grid-template-columns:300px minmax(0, 1fr); gap:20px; }
    .chat-sidebar, .chat-box { background:var(--panel); border:1px solid var(--border); border-radius:24px; box-shadow:var(--shadow); }
    .chat-sidebar { padding:16px; max-height:70vh; overflow-y:auto; overflow-x:hidden; }
    .chat-item { display:block; position:relative; padding:12px; margin-bottom:10px; border:1px solid transparent; border-radius:16px; color:inherit; text-decoration:none; background:#fff; }
    .chat-item:hover, .chat-item.active { background:rgba(243,197,71,.1); border-color:rgba(243,197,71,.3); }
    .chat-sidebar h3 { margin-bottom:14px; font-size:22px; }
    .chat-info h4 { margin-bottom:4px; font-size:14px; }
    .chat-info p { font-size:12px; color:var(--muted); }
    .role-badge { display:inline-block; margin-top:4px; padding:3px 8px; border-radius:999px; background:rgba(243,197,71,.15); color:#b07c00; font-size:10px; font-weight:700; }
    .unread-dot, .global-unread { background:#ef4444; color:#fff; border-radius:999px; font-size:11px; font-weight:700; }
    .unread-dot { position:absolute; top:8px; right:8px; min-width:18px; height:18px; display:flex; align-items:center; justify-content:center; padding:0 5px; }
    .global-unread { padding:2px 8px; margin-left:8px; }
    .chat-box { display:flex; flex-direction:column; height:70vh; min-height:560px; overflow:hidden; }
    .chat-header { padding:18px; border-bottom:1px solid var(--border); }
    .chat-header h3 { margin:0; font-size:24px; }
    .chat-messages { flex:1; min-height:0; overflow-y:auto; padding:18px; }
    .chat-msg { display:table; width:fit-content; max-width:78%; position:relative; margin:0 auto 12px 0; padding:12px 14px; border-radius:16px; white-space:pre-wrap; overflow-wrap:anywhere; }
    .chat-msg.sent { margin-left:auto; margin-right:0; background:rgba(243,197,71,.2); }
    .chat-msg.received { background:#f5f5f5; }
    .chat-msg .meta { display:block; margin-top:6px; color:var(--muted); font-size:11px; }
    .chat-footer { display:flex; gap:10px; margin-top:auto; padding:16px; border-top:1px solid var(--border); }
    .chat-footer textarea { flex:1; min-height:80px; padding:14px; border:1px solid var(--border); border-radius:16px; resize:vertical; background:#fff; color:var(--text); }
    .chat-footer button { align-self:flex-end; padding:14px 18px; border:0; border-radius:16px; background:#f3c547; color:#111; cursor:pointer; font-weight:700; }
    .message-alert { margin:16px 18px 0; padding:12px 16px; border-radius:16px; color:#b00; background:rgba(255,77,77,.12); }
    @media (max-width:900px) { .chat-container { grid-template-columns:1fr; } .chat-sidebar { max-height:none; } .chat-box { height:70vh; min-height:480px; } }
    </style>
@endsection
@section('content')
<section>
    <h2>Messages @if($globalUnread > 0)<span class="global-unread" id="globalUnreadBadge">{{ $globalUnread }}</span>@endif</h2>
    <div class="chat-container">
        <div class="chat-sidebar">
            <h3>Conversations</h3>
            @forelse($threads as $thread)
                @php($active = $selectedThread && $selectedThread['event_id'] === $thread['event_id'] && $selectedThread['user_id'] === $thread['user_id'])
                <a href="{{ route('supplier.messages', ['event_id' => $thread['event_id'], 'user_id' => $thread['user_id']]) }}" class="chat-item {{ $active ? 'active' : '' }}">
                    <div class="chat-info">
                        <h4>{{ $thread['name'] }}</h4>
                        <p>{{ $thread['event_title'] }} &bull; {{ $thread['event_date'] ?: 'No date' }}</p>
                        <span class="role-badge">{{ $thread['role'] }}</span>
                        @if($thread['unread'] > 0 && !$active)<span class="unread-dot">{{ $thread['unread'] }}</span>@endif
                    </div>
                </a>
            @empty
                <p style="color:var(--muted);">No conversations yet. When clients book your services, you can chat with them here.</p>
            @endforelse
        </div>

        <div class="chat-box">
            @if(!$selectedThread)
                <div class="chat-header"><h3>Select a conversation</h3></div>
            @else
                <div class="chat-header">
                    <h3>{{ $selectedThread['name'] }}</h3>
                </div>
                @if(session('message_error'))<div class="message-alert">{{ session('message_error') }}</div>@endif
                @if($errors->any())<div class="message-alert">{{ $errors->first('message') }}</div>@endif
                <div class="chat-messages" id="chatMessages">
                    <div class="chat-msg received" id="messageStatus">Loading messages...</div>
                </div>
                <form class="chat-footer" method="POST" action="{{ route('supplier.messages.send') }}">
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $selectedEventId }}">
                    <input type="hidden" name="recipient_id" value="{{ $selectedUserId }}">
                    <textarea name="message" placeholder="Type your message..." required>{{ old('message') }}</textarea>
                    <button type="submit"><i class="fas fa-paper-plane"></i> Send</button>
                </form>
            @endif
        </div>
    </div>
</section>
@endsection
@section('scripts')
@if($selectedThread)
<script>
    (() => {
        const messagesBox = document.getElementById('chatMessages');
        let lastId = Number(@json($lastMessageId));
    const apiUrl = @json(route('supplier.messages.api'));
    const eventId = @json($selectedEventId);
    const otherUserId = @json($selectedUserId);
    const currentUserId = @json((int) auth()->id());
    const otherName = @json($selectedThread['name']);

    function appendMessages(messages) {
            messages.forEach(message => {
                const messageId = Number(message.message_id || message.timestamp || 0);
                if (messagesBox.querySelector(`[data-mid="${messageId}"]`)) return;
                if (messageId <= lastId) return;
                lastId = messageId;
                const sent = Number(message.sender_id) === currentUserId;
                const item = document.createElement('div');
                item.className = `chat-msg ${sent ? 'sent' : 'received'}`;
                item.dataset.mid = messageId;
                const sender = document.createElement('strong');
                sender.textContent = message.sender_name || (sent ? 'You' : otherName);
                item.append(sender, document.createElement('br'));
                item.append(document.createTextNode(message.message || message.body || ''));
                const meta = document.createElement('span');
                meta.className = 'meta';
                meta.textContent = message.created_at || (message.timestamp ? new Date(Number(message.timestamp) * 1000).toLocaleString() : '');
                item.append(meta);
                messagesBox.appendChild(item);
            });
            messagesBox.scrollTop = messagesBox.scrollHeight;
        }

        async function poll() {
            try {
                const response = await fetch(`${apiUrl}?action=poll&event_id=${eventId}&other_user_id=${otherUserId}&last_id=${lastId}`, { headers: { Accept: 'application/json' } });
                const data = await response.json();
                if (Array.isArray(data.messages)) {
                    data.messages.sort((left, right) => Number(left.message_id || left.timestamp || 0) - Number(right.message_id || right.timestamp || 0));
                    appendMessages(data.messages);
                }
                const status = document.getElementById('messageStatus');
                if (status && data.messages && data.messages.length > 0) status.remove();
                if (status && Array.isArray(data.messages) && data.messages.length === 0) status.textContent = 'No messages yet. Start the conversation.';
            } catch (error) {
                console.error('Message polling failed', error);
            }
        }

    fetch(`${apiUrl}?action=mark_read&event_id=${eventId}&other_user_id=${otherUserId}`);
    messagesBox.scrollTop = messagesBox.scrollHeight;
        poll();
    window.setInterval(poll, 5000);
    })();
</script>
@endif
@endsection
