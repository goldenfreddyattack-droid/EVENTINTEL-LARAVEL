@extends('coordinator.layout')

@section('title', 'Messages')

@section('styles')
<style>
    .coordinator-chat { display:grid; grid-template-columns:300px minmax(0, 1fr); gap:20px; }
    .coordinator-chat .chat-sidebar, .coordinator-chat .chat-box { background:var(--panel); border:1px solid var(--border); border-radius:24px; box-shadow:var(--shadow); }
    .coordinator-chat .chat-sidebar { padding:16px; max-height:70vh; overflow-y:auto; overflow-x:hidden; }
    .coordinator-chat .chat-item { display:block; position:relative; padding:12px; margin-bottom:10px; border:1px solid transparent; border-radius:16px; color:inherit; text-decoration:none; background:#fff; }
    .coordinator-chat .chat-item:hover, .coordinator-chat .chat-item.active { background:rgba(243,197,71,.1); border-color:rgba(243,197,71,.3); }
    .coordinator-chat .chat-sidebar h3 { margin-bottom:14px; font-size:22px; }
    .coordinator-chat .chat-info h4 { margin-bottom:4px; font-size:14px; }
    .coordinator-chat .chat-info p { font-size:12px; color:var(--muted); }
    .coordinator-chat .role-badge { display:inline-block; margin-top:4px; padding:3px 8px; border-radius:999px; background:rgba(243,197,71,.15); color:#b07c00; font-size:10px; font-weight:700; }
    .coordinator-chat .chat-box { display:flex; flex-direction:column; height:70vh; min-height:560px; overflow:hidden; }
    .coordinator-chat .chat-header { padding:18px; border-bottom:1px solid var(--border); }
    .coordinator-chat .chat-header h3 { margin:0; font-size:24px; }
    .coordinator-chat .chat-messages { flex:1; min-height:0; overflow-y:auto; padding:18px; }
    .coordinator-chat .chat-msg { display:table; width:fit-content; max-width:78%; position:relative; margin:0 auto 12px 0; padding:12px 14px; border-radius:16px; overflow-wrap:anywhere; white-space:pre-wrap; }
    .coordinator-chat .chat-msg.sent { margin-left:auto; margin-right:0; background:rgba(243,197,71,.2); }
    .coordinator-chat .chat-msg.received { background:#f5f5f5; }
    .coordinator-chat .chat-empty { max-width:none; color:var(--muted); text-align:center; background:#f7f7f7; }
    .coordinator-chat .chat-msg .meta { display:block; margin-top:6px; color:var(--muted); font-size:11px; }
    .coordinator-chat .chat-footer { display:flex; gap:10px; margin-top:auto; padding:16px; border-top:1px solid var(--border); }
    .coordinator-chat .chat-footer textarea { flex:1; min-height:80px; padding:14px; border:1px solid var(--border); border-radius:16px; resize:vertical; background:#fff; color:var(--text); }
    .coordinator-chat .chat-footer button { align-self:flex-end; padding:14px 18px; border:0; border-radius:16px; background:#f3c547; color:#111; cursor:pointer; font-weight:700; }
    .coordinator-chat .message-alert { margin:16px 18px 0; padding:12px 16px; border-radius:16px; color:#b00; background:rgba(255,77,77,.12); }
    @media (max-width:900px) { .coordinator-chat { grid-template-columns:1fr; } .coordinator-chat .chat-sidebar { max-height:none; } .coordinator-chat .chat-box { height:70vh; min-height:480px; } }
</style>
@endsection

@section('content')
<section>
    <h2>Messages</h2>
    <div class="coordinator-chat">
        <div class="chat-sidebar">
            <h3>Conversations</h3>
            @forelse($threads as $thread)
                @php($active = $selectedThread && $selectedThread['event_id'] === $thread['event_id'] && $selectedThread['user_id'] === $thread['user_id'])
                <a href="{{ route('coordinator.messages', ['event_id' => $thread['event_id'], 'user_id' => $thread['user_id']]) }}" class="chat-item {{ $active ? 'active' : '' }}">
                    <div class="chat-info"><h4>{{ $thread['name'] }}</h4><p>{{ $thread['event_title'] }} &bull; {{ $thread['event_date'] ?: 'No date' }}</p><span class="role-badge">{{ $thread['role'] }}</span></div>
                </a>
            @empty
                <p style="color:var(--muted);">No conversations yet. Assigned clients and suppliers will appear here.</p>
            @endforelse
        </div>
        <div class="chat-box">
            @if(!$selectedThread)
                <div class="chat-header"><h3>Select a conversation</h3></div>
            @else
                <div class="chat-header"><h3>{{ $selectedThread['name'] }}</h3><span class="role-badge">{{ $selectedThread['role'] }}</span></div>
                @if($errors->any())<div class="message-alert">{{ $errors->first('message') }}</div>@endif
                <div class="chat-messages" id="coordinatorChatMessages">
                    @forelse($messages as $message)
                        <div class="chat-msg {{ (int)($message['sender_id'] ?? 0) === (int) auth()->id() ? 'sent' : 'received' }}" data-mid="{{ $message['message_id'] ?? $message['timestamp'] ?? 0 }}">{{ $message['message'] ?? $message['body'] ?? '' }}<span class="meta">{{ $message['created_at'] ?? '' }}</span></div>
                    @empty
                        <div class="chat-msg chat-empty received" id="coordinatorMessageStatus">No message sent yet. Start the conversation.</div>
                    @endforelse
                </div>
                <form class="chat-footer" method="POST" action="{{ route('coordinator.messages') }}">@csrf<input type="hidden" name="event_id" value="{{ $eventId }}"><input type="hidden" name="recipient_id" value="{{ $otherUserId }}"><textarea name="message" placeholder="Type your message..." required>{{ old('message') }}</textarea><button type="submit"><i class="fas fa-paper-plane"></i> Send</button></form>
            @endif
        </div>
    </div>
</section>
@endsection

@section('scripts')
@if($selectedThread)
<script>
(() => {
    const box = document.getElementById('coordinatorChatMessages');
    let lastId = Number(@json($lastMessageId));
    const api = @json(route('coordinator.messages.api'));
    const eventId = @json($eventId);
    const otherUserId = @json($otherUserId);
    const currentUserId = @json((int) auth()->id());
    function append(messages) {
        messages.forEach(message => {
            const id = Number(message.message_id || message.timestamp || 0);
            if (id <= lastId || box.querySelector(`[data-mid="${id}"]`)) return;
            lastId = id;
            const item = document.createElement('div');
            item.className = `chat-msg ${Number(message.sender_id) === currentUserId ? 'sent' : 'received'}`;
            item.dataset.mid = id;
            item.textContent = message.message || message.body || '';
            const meta = document.createElement('span');
            meta.className = 'meta';
            meta.textContent = message.created_at || (message.timestamp ? new Date(Number(message.timestamp) * 1000).toLocaleString() : '');
            item.appendChild(meta);
            box.appendChild(item);
        });
        const status = document.getElementById('coordinatorMessageStatus');
        if (status && messages.length) status.remove();
        box.scrollTop = box.scrollHeight;
    }
    async function poll() {
        try {
            const response = await fetch(`${api}?action=poll&event_id=${eventId}&other_user_id=${otherUserId}&last_id=${lastId}`, { headers: { Accept: 'application/json' } });
            const data = await response.json();
            if (Array.isArray(data.messages)) append(data.messages);
        } catch (error) { console.error('Coordinator message polling failed', error); }
    }
    fetch(`${api}?action=mark_read&event_id=${eventId}&other_user_id=${otherUserId}`);
    box.scrollTop = box.scrollHeight;
    window.setInterval(poll, 5000);
})();
</script>
@endif
@endsection
