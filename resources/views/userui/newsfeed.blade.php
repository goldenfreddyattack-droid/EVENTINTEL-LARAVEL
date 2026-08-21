<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EventIntel - Newsfeed</title>
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/userui/navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/userui/newsfeed.css') }}">
</head>
<body>
    <div class="newsfeed-page">
        @include('userui.partials.navbar', ['active' => 'newsfeed'])

        <main class="newsfeed-content">
            <header class="newsfeed-heading">
                <p class="newsfeed-eyebrow">Community updates</p>
                <h1>Event Newsfeed</h1>
                <p>Share ideas, discover inspiration, and keep up with the EventIntel community.</p>
            </header>

            @if (session('success'))
                <div class="newsfeed-message">{{ session('success') }}</div>
            @endif

            <section class="create-post-card">
                <form method="POST" action="{{ route('newsfeed.store') }}" enctype="multipart/form-data" id="postForm">
                    @csrf
                    <div class="post-composer">
                        <div class="newsfeed-avatar"><i class="fas fa-user" aria-hidden="true"></i></div>
                        <textarea name="content" id="postContent" placeholder="What's on your mind for your event?">{{ old('content') }}</textarea>
                    </div>
                    @error('content')<p class="field-error">{{ $message }}</p>@enderror
                    @error('post_image')<p class="field-error">{{ $message }}</p>@enderror
                    <div class="composer-footer">
                        <label class="photo-button" for="imageInput"><i class="fas fa-image" aria-hidden="true"></i> Add photo</label>
                        <input type="file" name="post_image" id="imageInput" accept="image/*">
                        <span class="file-name" id="fileName">No photo selected</span>
                        <button class="post-submit" type="submit" id="submitButton" disabled>Share post</button>
                    </div>
                    <div id="imagePreview" class="image-preview"></div>
                </form>
            </section>

            <section class="feed-list" aria-label="Community posts">
                @forelse ($posts as $post)
                    @php($author = $post->full_name ?: $post->name ?: $post->username ?: 'EventIntel member')
                    <article class="post-card" data-post-id="{{ $post->post_id }}">
                        <header class="post-header">
                            <div class="post-author">
                                <div class="newsfeed-avatar"><i class="fas fa-user" aria-hidden="true"></i></div>
                                <div><strong>{{ $author }}</strong><time>{{ \Carbon\Carbon::parse($post->created_at)->format('M j, Y g:i A') }}</time></div>
                            </div>
                            <i class="fas fa-ellipsis" aria-hidden="true"></i>
                        </header>
                        <div class="post-body">
                            @if ($post->content)<p class="post-text">{{ $post->content }}</p>@endif
                            @if ($post->image_path ?? null)<img class="post-image" src="{{ asset('uploads/' . $post->image_path) }}" alt="Post image">@endif
                        </div>
                        <div class="comments" id="comments-{{ $post->post_id }}">
                            @foreach (collect($comments[$post->post_id] ?? []) as $comment)
                                @php($commentAuthor = $comment->full_name ?: $comment->name ?: $comment->username ?: 'Member')
                                <div class="comment"><strong>{{ $commentAuthor }}</strong><time>{{ \Carbon\Carbon::parse($comment->created_at)->format('M j, Y g:i A') }}</time><p>{{ $comment->comment }}</p></div>
                            @endforeach
                        </div>
                        <form class="comment-form" data-post-id="{{ $post->post_id }}">
                            <input name="comment" placeholder="Write a comment..." maxlength="2000" required>
                            <button type="submit">Comment</button>
                        </form>
                        <footer class="post-footer">
                            <button class="like-button {{ in_array($post->post_id, $likedPostIds) ? 'liked' : '' }}" data-post-id="{{ $post->post_id }}"><i class="{{ in_array($post->post_id, $likedPostIds) ? 'fas' : 'far' }} fa-heart"></i> <span class="like-label">{{ in_array($post->post_id, $likedPostIds) ? 'Unlike' : 'Like' }}</span> <span class="like-count">{{ $post->likes_count }}</span></button>
                            <span><i class="far fa-comment" aria-hidden="true"></i> <span class="comment-count">{{ $post->comments_count }}</span> comments</span>
                            <button type="button" class="share-button" data-url="{{ url('/newsfeed') }}#post-{{ $post->post_id }}"><i class="far fa-share" aria-hidden="true"></i> Share</button>
                        </footer>
                    </article>
                @empty
                    <div class="empty-state"><i class="fas fa-comment-dots" aria-hidden="true"></i><h2>No posts yet</h2><p>Be the first to share what's happening with your events.</p></div>
                @endforelse
            </section>
        </main>
    </div>

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
            preview.innerHTML = imageInput.files[0] ? `<img src="${URL.createObjectURL(imageInput.files[0])}" alt="Selected image">` : '';
        });

        document.querySelectorAll('.like-button').forEach(button => button.addEventListener('click', async () => {
            const response = await fetch(@json(route('newsfeed.like')), { method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'}, body: JSON.stringify({post_id: button.dataset.postId}) });
            const data = await response.json();
            button.classList.toggle('liked', data.liked);
            button.querySelector('i').className = `${data.liked ? 'fas' : 'far'} fa-heart`;
            button.querySelector('.like-label').textContent = data.liked ? 'Unlike' : 'Like';
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
            document.getElementById(`comments-${form.dataset.postId}`).insertAdjacentHTML('beforeend', `<div class="comment"><strong>${author}</strong><time>Just now</time><p>${comment.comment}</p></div>`);
            form.closest('.post-card').querySelector('.comment-count').textContent = Number(form.closest('.post-card').querySelector('.comment-count').textContent) + 1;
            input.value = '';
        }));

        document.querySelectorAll('.share-button').forEach(button => button.addEventListener('click', async () => {
            await navigator.clipboard?.writeText(button.dataset.url);
            button.innerHTML = '<i class="fas fa-check"></i> Copied';
            setTimeout(() => button.innerHTML = '<i class="far fa-share"></i> Share', 1600);
        }));
    </script>
</body>
</html>
