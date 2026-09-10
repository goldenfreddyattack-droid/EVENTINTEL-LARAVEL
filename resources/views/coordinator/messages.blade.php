@extends('coordinator.layout')

@section('title', 'Messages')

@section('styles')
<style>
    /* ===== Messenger Center Wrapper & Outer Card Container ===== */
    .messenger-card { max-width: 980px; margin: 0 auto; background: #ffffff; border: 1px solid #ebebeb; border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03); overflow: hidden; height: 80vh; min-height: 560px; max-height: 800px; display: flex; flex-direction: column; }
    
    /* Top Header Bar */
    .messenger-header { padding: 16px 24px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between; background: #ffffff; }
    .messenger-header h2 { font-size: 20px; font-weight: 700; color: var(--text); margin: 0; display: flex; align-items: center; gap: 8px; }

    /* Unified Messenger Grid */
    .messenger-body { display: grid; grid-template-columns: 320px minmax(0, 1fr); flex: 1; min-height: 0; }

    /* Left Sidebar: Thread List */
    .messenger-sidebar { border-right: 1px solid #f0f0f0; overflow-y: auto; background: #ffffff; display: flex; flex-direction: column; }
    .sidebar-title { padding: 14px 18px 10px; font-size: 12px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #fafafa; margin: 0; }
    .thread-list { display: flex; flex-direction: column; }
    .thread-item { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid #f9f9f9; text-decoration: none; color: inherit; transition: background 0.15s ease; position: relative; }
    .thread-item:hover { background: #fcfcfc; }
    .thread-item.active { background: rgba(243, 197, 71, 0.12); }
    .thread-details h4 { margin: 0 0 3px; font-size: 14px; font-weight: 600; color: var(--text); }
    .thread-details p { margin: 0; font-size: 12px; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 220px; }
    .role-pill { display: inline-block; margin-top: 4px; padding: 2px 6px; border-radius: 4px; background: rgba(0,0,0,0.05); color: var(--muted); font-size: 10px; font-weight: 600; text-transform: uppercase; }

    /* Right Main Chat Window */
    .messenger-chat-box { display: flex; flex-direction: column; background: #ffffff; height: 100%; min-height: 0; }
    .chat-top-bar { padding: 14px 20px; border-bottom: 1px solid #f0f0f0; background: #ffffff; display: flex; align-items: center; justify-content: space-between; }
    .chat-top-bar h3 { margin: 0; font-size: 16px; font-weight: 700; color: var(--text); }

    /* Message Stream */
    .chat-stream { flex: 1; min-height: 0; overflow-y: auto; padding: 20px; background: #fdfdfd; display: flex; flex-direction: column; gap: 8px; }
    .msg-bubble { max-width: 68%; padding: 10px 14px; border-radius: 18px; font-size: 13.5px; line-height: 1.45; position: relative; word-wrap: break-word; }
    .msg-bubble.sent { align-self: flex-end; background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208); color: #111; border-bottom-right-radius: 4px; font-weight: 500; }
    .msg-bubble.received { align-self: flex-start; background: #f0f0f0; color: var(--text); border-bottom-left-radius: 4px; }
    .msg-meta { display: block; font-size: 10px; margin-top: 4px; opacity: 0.65; text-align: right; }

    /* Input Footer */
    .messenger-input-row { padding: 12px 16px; border-top: 1px solid #f0f0f0; background: #ffffff; display: flex; align-items: center; gap: 10px; }
    .messenger-input-row textarea { flex: 1; height: 42px; min-height: 42px; max-height: 100px; padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 22px; resize: none; background: #f9f9f9; color: var(--text); font-size: 13.5px; outline: none; transition: border-color 0.2s ease, background 0.2s ease; }
    .messenger-input-row textarea:focus { border-color: var(--gold); background: #ffffff; }
    .send-btn-circle { width: 42px; height: 42px; border-radius: 50%; border: none; background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208); color: #111; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; transition: transform 0.2s ease; }
    .send-btn-circle:hover { transform: scale(1.05); }

    /* Alerts & Empty States */
    .chat-alert { margin: 10px 16px 0; padding: 8px 12px; border-radius: 8px; color: #d9534f; background: rgba(217,83,79,0.08); font-size: 12px; font-weight: 600; }
    .empty-messenger { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center; color: var(--muted); padding: 20px; }
    .empty-messenger i { font-size: 36px; color: #ddd; margin-bottom: 10px; }

    @media (max-width: 768px) { .messenger-body { grid-template-columns: 1fr; } .messenger-sidebar { display: none; } }
</style>
@endsection

@section('content')
<section class="messenger-card">
    <header class="messenger-header">
        <h2><i class="fas fa-comments text-gold"></i> Messages</h2>
    </header>

    <div class="messenger-body">
        {{-- Left Sidebar --}}
        <aside class="messenger-sidebar">
            <h4 class="sidebar-title">Chats</h4>
            <div class="thread-list">
                @forelse($threads as $thread)
                    @php($active = $selectedThread && $selectedThread['event_id'] === $thread['event_id'] && $selectedThread['user_id'] === $thread['user_id'])
                    <a href="{{ route('coordinator.messages', ['event_id' => $thread['event_id'], 'user_id' => $thread['user_id']]) }}" class="thread-item {{ $active ? 'active' : '' }}">
                        <div class="thread-details">
                            <h4>{{ $thread['name'] }}</h4>
                            <p>{{ $thread['event_title'] }} &bull; {{ $thread['event_date'] ?: 'No date' }}</p>
                            <span class="role-pill">{{ $thread['role'] }}</span>
                        </div>
                    </a>
                @empty
                    <div class="empty-messenger">
                        <i class="fas fa-inbox"></i>
                        <p>No conversations yet.</p>
                    </div>
                @endforelse
            </div>
        </aside>

        {{-- Right Chat Area --}}
        <main class="messenger-chat-box">
            @if(!$selectedThread)
                <div class="empty-messenger">
                    <i class="far fa-paper-plane"></i>
                    <h4>Select a conversation</h4>
                    <p>Choose a chat from the left sidebar to start messaging.</p>
                </div>
            @else
                <div class="chat-top-bar">
                    <h3>{{ $selectedThread['name'] }}</h3>
                    <span class="role-pill">{{ $selectedThread['role'] }}</span>
                </div>

                @if($errors->any())
                    <div class="chat-alert">{{ $errors->first('message') }}</div>
                @endif

                <div class="chat-stream" id="coordinatorChatMessages">
                    @forelse($messages as $message)
                        <div class="msg-bubble {{ (int)($message['sender_id'] ?? 0) === (int) auth()->id() ? 'sent' : 'received' }}" data-mid="{{ $message['message_id'] ?? $message['timestamp'] ?? 0 }}">
                            {{ $message['message'] ?? $message['body'] ?? '' }}
                            <span class="msg-meta">{{ $message['created_at'] ?? '' }}</span>
                        </div>
                    @empty
                        <div class="msg-bubble received" id="coordinatorMessageStatus">No message sent yet. Start the conversation.</div>
                    @endforelse
                </div>

                <form class="messenger-input-row" method="POST" action="{{ route('coordinator.messages') }}">
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $eventId }}">
                    <input type="hidden" name="recipient_id" value="{{ $otherUserId }}">
                    <textarea name="message" placeholder="Type your message..." required>{{ old('message') }}</textarea>
                    <button type="submit" class="send-btn-circle" title="Send message">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            @endif
        </main>
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
            item.className = `msg-bubble ${Number(message.sender_id) === currentUserId ? 'sent' : 'received'}`;
            item.dataset.mid = id;
            item.textContent = message.message || message.body || '';
            const meta = document.createElement('span');
            meta.className = 'msg-meta';
            meta.textContent = message.created_at || (message.timestamp ? new Date(Number(message.timestamp) * 1000).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '');
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