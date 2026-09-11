@extends('coordinator.layout')

@section('title', 'EventIntel - Newsfeed')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/userui/newsfeed.css') }}">
    <style>
        /* ===== Floating Container Override ===== */
        .coord-container { max-width: 980px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; padding-top: 4px; }
        .coord-card-floating { background: #ffffff; border: 1px solid #ebebeb; border-radius: 16px; padding: 20px 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.02); }
        
        .coord-header-bar { border-bottom: 1px solid #f0f0f0; padding-bottom: 12px; margin-bottom: 16px; }
        .coord-header-bar h2 { font-size: 22px; font-weight: 700; color: var(--text); margin: 0 0 2px; }
        .coord-header-bar p { color: var(--muted); font-size: 13px; margin: 0; }

        .alert-success-box { background: rgba(46,159,77,0.08); border: 1px solid rgba(46,159,77,0.2); color: #2a8a43; padding: 10px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 16px; }

        /* Compact Composer Inside Floating Card */
        .create-post-card { background: #fafafa; border: 1px solid #f0f0f0; border-radius: 14px; padding: 16px; margin-bottom: 20px; }
        .post-composer textarea { width: 100%; border: 1px solid #e0e0e0; border-radius: 10px; padding: 10px 12px; background: #ffffff; font-size: 13px; outline: none; }
        .composer-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; flex-wrap: wrap; gap: 10px; }
        .post-submit { border: none; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208); color: #111; cursor: pointer; }
        .post-submit:disabled { opacity: 0.5; cursor: not-allowed; }
    </style>
@endsection

@section('content')
<div class="coord-container">
    <div class="coord-card-floating">
        <header class="coord-header-bar">
            <h2><i class="fas fa-newspaper text-gold"></i> Event Newsfeed</h2>
            <p>Share ideas, discover inspiration, and keep up with the EventIntel community.</p>
        </header>

        @if (session('success'))
            <div class="alert-success-box"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        {{-- Post Composer --}}
        <section class="create-post-card">
            <form method="POST" action="{{ route('newsfeed.store') }}" enctype="multipart/form-data" id="postForm">
                @csrf
                <div class="post-composer">
                    <textarea name="content" id="postContent" rows="3" placeholder="What's on your mind for your event?">{{ old('content') }}</textarea>
                </div>
                @error('content')<p class="field-error" style="color:#d9534f; font-size:12px; margin-top:4px;">{{ $message }}</p>@enderror
                @error('post_image')<p class="field-error" style="color:#d9534f; font-size:12px; margin-top:4px;">{{ $message }}</p>@enderror
                
                <div class="composer-footer">
                    <label class="photo-button" for="imageInput" style="font-size:12px; cursor:pointer; font-weight:600; color:var(--text);"><i class="fas fa-image text-gold"></i> Add photo</label>
                    <input type="file" name="post_image" id="imageInput" accept="image/*" style="display:none;">
                    <span class="file-name" id="fileName" style="font-size:12px; color:var(--muted);">No photo selected</span>
                    <button class="post-submit" type="submit" id="submitButton" disabled>Share post</button>
                </div>
                <div id="imagePreview" class="image-preview" style="margin-top:10px;"></div>
            </form>
        </section>

        {{-- Feed Stream --}}
        <section class="feed-list" aria-label="Community posts">
            @forelse ($posts as $post)
                @php($author = $post->full_name ?: $post->name ?: $post->username ?: 'EventIntel member')
                <article class="post-card" data-post-id="{{ $post->post_id }}" style="background:#fafafa; border:1px solid #ebebeb; border-radius:14px; padding:16px; margin-bottom:16px;">
                    <header class="post-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <div class="post-author" style="display:flex; align-items:center; gap:10px;">
                            <div class="newsfeed-avatar"><i class="fas fa-user-circle" style="font-size:24px; color:var(--gold);"></i></div>
                            <div>
                                <strong style="font-size:14px; display:block;">{{ $author }}</strong>
                                <time style="font-size:11px; color:var(--muted);">{{ \Carbon\Carbon::parse($post->created_at)->format('M j, Y g:i A') }}</time>
                            </div>
                        </div>
                    </header>
                    <div class="post-body">
                        @if ($post->content)<p class="post-text" style="font-size:13px; line-height:1.4;">{{ $post->content }}</p>@endif
                        @if ($post->image_path ?? null)<img class="post-image" src="{{ asset('uploads/' . $post->image_path) }}" alt="Post image" style="width:100%; max-height:300px; object-fit:cover; border-radius:10px; margin-top:8px;">@endif
                    </div>
                    <div class="comments" id="comments-{{ $post->post_id }}" style="margin-top:10px; font-size:12px;">
                        @foreach (collect($comments[$post->post_id] ?? []) as $comment)
                            @php($commentAuthor = $comment->full_name ?: $comment->name ?: $comment->username ?: 'Member')
                            <div class="comment" style="background:#ffffff; border:1px solid #f0f0f0; border-radius:8px; padding:8px 10px; margin-bottom:6px;">
                                <strong>{{ $commentAuthor }}</strong> <time style="color:var(--muted); font-size:10px;">{{ \Carbon\Carbon::parse($comment->created_at)->format('M j, Y g:i A') }}</time>
                                <p style="margin:2px 0 0;">{{ $comment->comment }}</p>
                            </div>
                        @endforeach
                    </div>
                    <form class="comment-form" data-post-id="{{ $post->post_id }}" style="display:flex; gap:8px; margin-top:10px;">
                        <input name="comment" placeholder="Write a comment..." maxlength="2000" required style="flex:1; border:1px solid #e0e0e0; border-radius:8px; padding:6px 10px; font-size:12px;">
                        <button type="submit" style="border:none; background:var(--gold); color:#111; border-radius:8px; padding:6px 12px; font-size:12px; font-weight:700; cursor:pointer;">Comment</button>
                    </form>
                    <footer class="post-footer" style="display:flex; gap:16px; margin-top:12px; border-top:1px solid #f0f0f0; padding-top:10px; font-size:12px; color:var(--muted);">
                        <button class="like-button {{ in_array($post->post_id, $likedPostIds) ? 'liked' : '' }}" data-post-id="{{ $post->post_id }}" aria-label="Like post" style="background:none; border:none; cursor:pointer; color:inherit; font-size:12px;"><i class="{{ in_array($post->post_id, $likedPostIds) ? 'fas' : 'far' }} fa-heart text-gold"></i> <span class="like-count">{{ $post->likes_count }}</span></button>
                        <span><i class="far fa-comment"></i> <span class="comment-count">{{ $post->comments_count }}</span> comments</span>
                        <button type="button" class="share-button" data-url="{{ url('/coordinator/newsfeed') }}#post-{{ $post->post_id }}" style="background:none; border:none; cursor:pointer; color:inherit; font-size:12px;"><i class="fas fa-share-nodes"></i> Share</button>
                    </footer>
                </article>
            @empty
                <div style="text-align:center; padding:40px; color:var(--muted); font-size:13px;">
                    <i class="fas fa-comment-dots" style="font-size:32px; color:#ccc; margin-bottom:8px;"></i>
                    <h4>No posts yet</h4>
                    <p>Be the first to share what's happening with your events.</p>
                </div>
            @endforelse
        </section>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const imageInput = document.getElementById('imageInput');
    const contentInput = document.getElementById('postContent');
    const submitButton = document.getElementById('submitButton');
    const preview = document.getElementById('imagePreview');

    function updateComposer() {
        submitButton.disabled = !contentInput.value.trim() && !imageInput.files.length;
        document.getElementById('fileName').textContent = imageInput.files[0]?.name || 'No photo selected';
    }
    contentInput.addEventListener('input', updateComposer);
    imageInput.addEventListener('change', () => {
        updateComposer();
        preview.innerHTML = imageInput.files[0] ? `<img src="${URL.createObjectURL(imageInput.files[0])}" alt="Selected image" style="max-height:150px; border-radius:8px;">` : '';
    });

    document.querySelectorAll('.like-button').forEach(button => button.addEventListener('click', async () => {
        const response = await fetch(@json(route('newsfeed.like')), { method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'}, body: JSON.stringify({post_id: button.dataset.postId}) });
        const data = await response.json();
        button.classList.toggle('liked', data.liked);
        button.querySelector('i').className = `${data.liked ? 'fas' : 'far'} fa-heart text-gold`;
        button.querySelector('.like-count').textContent = data.likes;
    }));

    document.querySelectorAll('.comment-form').forEach(form => form.addEventListener('submit', async event => {
        event.preventDefault();
        const input = form.querySelector('input');
        const response = await fetch(@json(route('newsfeed.comment')), { method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'}, body: JSON.stringify({post_id: form.dataset.postId, comment: input.value}) });
        if (!response.ok) return;
        const data = await response.json();
        const comment = data.comment;
        const author = comment.full_name || comment.name || comment.username || 'Member';
        document.getElementById(`comments-${form.dataset.postId}`).insertAdjacentHTML('beforeend', `<div class="comment" style="background:#ffffff; border:1px solid #f0f0f0; border-radius:8px; padding:8px 10px; margin-bottom:6px;"><strong>${author}</strong> <time style="color:var(--muted); font-size:10px;">Just now</time><p style="margin:2px 0 0;">${comment.comment}</p></div>`);
        form.closest('.post-card').querySelector('.comment-count').textContent = Number(form.closest('.post-card').querySelector('.comment-count').textContent) + 1;
        input.value = '';
    }));

    document.querySelectorAll('.share-button').forEach(button => button.addEventListener('click', async () => {
        await navigator.clipboard?.writeText(button.dataset.url);
        button.innerHTML = '<i class="fas fa-check"></i> Copied';
        setTimeout(() => button.innerHTML = '<i class="fas fa-share-nodes"></i> Share', 1600);
    }));
</script>
@endsection