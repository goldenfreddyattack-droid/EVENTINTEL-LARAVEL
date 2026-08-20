<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Use absolute path to config
$config_path = realpath(__DIR__ . '/../../config/db.php');
if (!$config_path) {
    die('Configuration file not found');
}
require_once $config_path;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$pdo = db();
$user_id = $_SESSION['user_id'];

// Handle new post creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_post') {
    $content = $_POST['content'] ?? '';
    $image_path = null;
    
    if (!empty($content)) {
        // Handle image upload if provided
        if (isset($_FILES['post_image']) && $_FILES['post_image']['size'] > 0) {
            $uploads_dir = __DIR__ . '/../../uploads/posts/';
            if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);
            
            $file_ext = pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION);
            $safe_filename = 'post_' . $user_id . '_' . time() . '.' . $file_ext;
            $file_path = $uploads_dir . $safe_filename;
            
            if (move_uploaded_file($_FILES['post_image']['tmp_name'], $file_path)) {
                $image_path = 'posts/' . $safe_filename;
            }
        }
        
        // Insert post
        $stmt = $pdo->prepare("INSERT INTO user_posts (user_id, content, image_path) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $content, $image_path]);
    }
    
    header('Location: newsfeed.php');
    exit;
}

// Fetch user posts with user info and counts
$posts = $pdo->query(
    "SELECT p.*, u.full_name, u.face_capture, 
            COALESCE((SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = p.post_id), 0) AS likes_count, 
            COALESCE((SELECT COUNT(*) FROM post_comments pc WHERE pc.post_id = p.post_id), 0) AS comments_count 
     FROM user_posts p 
     JOIN users u ON p.user_id = u.user_id 
     ORDER BY p.created_at DESC"
)->fetchAll();

// Get current user info
$current_user = $pdo->prepare("SELECT full_name, face_capture FROM users WHERE user_id = ?");
$current_user->execute([$user_id]);
$current_user = $current_user->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventIntel - Newsfeed</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f5f5 0%, #ffffff 100%);
            color: #222;
            min-height: 100vh;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        /* NAVBAR */
        .navbar {
            width: 100%;
            padding: 12px 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .navbar-brand {
            font-size: 26px;
            font-weight: 800;
            color: #f3c547;
            letter-spacing: 1px;
        }

        .navbar-nav {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .navbar-nav button {
            padding: 8px 18px;
            border: 1px solid rgba(212,160,23,0.35);
            background: rgba(255,255,255,0.55);
            color: #222;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.3s ease;
            font-size: 14px;
        }

        .navbar-nav button:hover,
        .navbar-nav button.active {
            background: linear-gradient(to right, #ffe17a, #d4a017);
            color: black;
            box-shadow: 0 0 14px rgba(255, 215, 0, 0.12);
        }

        .profile-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(243,197,71,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f3c547;
            cursor: pointer;
            border: 1px solid rgba(243,197,71,0.3);
        }

        /* CREATE POST CARD */
        .create-post-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(243,197,71,0.1);
        }

        .post-input-wrapper {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(243,197,71,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f3c547;
            font-size: 18px;
            flex-shrink: 0;
        }

        .post-input {
            flex: 1;
            padding: 12px;
            border: 1px solid rgba(243,197,71,0.15);
            border-radius: 20px;
            background: #f9f9f9;
            color: #222;
            cursor: pointer;
            transition: 0.3s;
        }

        .post-input:hover {
            border-color: rgba(243,197,71,0.3);
            background: #fafafa;
        }

        .post-input:focus {
            outline: none;
            border-color: #f3c547;
            background: white;
        }

        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            padding-top: 12px;
            border-top: 1px solid rgba(243,197,71,0.08);
        }

        .post-action-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            background: transparent;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 13px;
            transition: 0.3s;
            border-radius: 6px;
        }

        .post-action-btn:hover {
            background: rgba(243,197,71,0.1);
            color: #f3c547;
        }

        #image-input {
            display: none;
        }

        .post-submit-btn {
            padding: 8px 20px;
            background: #f3c547;
            color: black;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
            font-size: 13px;
        }

        .post-submit-btn:hover {
            background: #e8b70f;
            box-shadow: 0 4px 12px rgba(243,197,71,0.3);
        }

        .post-submit-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        /* POST CARD */
        .post-card {
            background: white;
            border-radius: 12px;
            margin-bottom: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(243,197,71,0.1);
            overflow: hidden;
        }

        .post-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-bottom: 1px solid rgba(243,197,71,0.08);
        }

        .post-author {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .post-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(243,197,71,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f3c547;
            font-size: 18px;
        }

        .post-author-info h4 {
            margin: 0;
            font-size: 14px;
            color: #222;
        }

        .post-author-info .time {
            font-size: 12px;
            color: #999;
            margin-top: 2px;
        }

        .post-content {
            padding: 16px;
        }

        .post-text {
            color: #333;
            line-height: 1.6;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .post-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 8px;
        }

        .post-footer {
            display: flex;
            justify-content: space-around;
            padding: 12px 0;
            border-top: 1px solid rgba(243,197,71,0.08);
            color: #999;
            font-size: 13px;
        }

        .post-footer-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px;
            background: transparent;
            border: none;
            color: #999;
            cursor: pointer;
            transition: 0.3s;
            border-radius: 6px;
        }

        .post-footer-btn:hover {
            background: rgba(243,197,71,0.1);
            color: #f3c547;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 48px;
            color: rgba(243,197,71,0.2);
            margin-bottom: 12px;
        }

        .empty-state p {
            font-size: 14px;
        }

        @media (max-width: 640px) {
            .container {
                max-width: 100%;
                padding: 10px;
            }

            .navbar {
                padding: 12px 16px;
            }

            .navbar-brand {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-brand">EventIntel</div>
        <div class="navbar-nav">
            <?php 
            // Show different navigation based on user role
            $role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : 'client';
            
            if ($role === 'supplier') {
                echo '<button onclick="window.location.href=\'../../Supplier/DASHBOARD.php\'">Dashboard</button>';
                echo '<button onclick="window.location.href=\'../../Supplier/SERVICES.php\'">Services</button>';
                echo '<button onclick="window.location.href=\'../../Supplier/BOOKINGS.php\'">Bookings</button>';
            } elseif ($role === 'coordinator') {
                echo '<button onclick="window.location.href=\'../../coordinator/DASHBOARD.php\'">Dashboard</button>';
                echo '<button onclick="window.location.href=\'../../coordinator/ASSIGNED_EVENTS.php\'">Events</button>';
                echo '<button onclick="window.location.href=\'../../coordinator/MYSUPPLIERS.php\'">Suppliers</button>';
            } else {
                // Default client navigation
                echo '<button onclick="window.location.href=\'homepage.php\'">Home</button>';
                echo '<button onclick="window.location.href=\'createevent.php\'">Create Event</button>';
                echo '<button onclick="window.location.href=\'recommendation.php\'">Recommendations</button>';
                echo '<button onclick="window.location.href=\'profile.php\'">Profile</button>';
            }
            ?>
            <button class="active" onclick="window.location.href='newsfeed.php'">Newsfeed</button>
            <button onclick="window.location.href='../../auth/logout.php'" style="color: #ff6b6b;">Logout</button>
        </div>
    </div>

    <div class="container">
        <!-- CREATE POST SECTION -->
        <div class="create-post-card">
            <form id="postForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create_post">
                
                <div class="post-input-wrapper">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <textarea 
                        id="postContent"
                        name="content" 
                        class="post-input" 
                        placeholder="What's on your mind for your event?" 
                        style="resize: vertical; min-height: 40px; max-height: 120px; border-radius: 10px; padding: 12px; border: 1px solid rgba(243,197,71,0.15); font-family: 'Segoe UI', sans-serif;"
                    ></textarea>
                </div>

                <div class="post-actions">
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="post-action-btn" onclick="document.getElementById('image-input').click()">
                            <i class="fas fa-image"></i>
                            Photo
                        </button>
                        <button type="button" class="post-action-btn" onclick="document.getElementById('postContent').focus()">
                            <i class="fas fa-heart"></i>
                            Feeling
                        </button>
                    </div>
                    <button type="submit" class="post-submit-btn" id="submitBtn" disabled>Post</button>
                </div>

                <input type="file" id="image-input" name="post_image" accept="image/*" onchange="enableSubmitBtn()">
                <div id="preview" style="margin-top: 12px; position: relative;"></div>
            </form>
        </div>

        <!-- FEED SECTION -->
        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <i class="fas fa-comment-dots"></i>
                <p><strong>No posts yet!</strong></p>
                <p>Be the first to share what's happening with your events.</p>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="post-card">
                    <div class="post-header">
                        <div class="post-author">
                            <div class="post-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="post-author-info">
                                <h4><?= esc($post['full_name']) ?></h4>
                                <span class="time"><?= date('M j, Y g:i A', strtotime($post['created_at'])) ?></span>
                            </div>
                        </div>
                        <button style="background: none; border: none; color: #999; cursor: pointer; font-size: 14px;">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                    </div>

                    <div class="post-content">
                        <div class="post-text"><?= esc($post['content']) ?></div>
                        <?php if ($post['image_path']): ?>
                            <img src="../../uploads/<?= esc($post['image_path']) ?>" alt="Post image" class="post-image">
                        <?php endif; ?>
                    </div>

                    <?php
                        // fetch comments for this post
                        $cstmt = $pdo->prepare("SELECT c.*, u.full_name FROM post_comments c JOIN users u ON c.user_id = u.user_id WHERE c.post_id = ? ORDER BY c.created_at ASC");
                        $cstmt->execute([$post['post_id']]);
                        $comments = $cstmt->fetchAll();
                    ?>

                    <div class="comments" style="padding: 0 16px 12px;">
                        <?php foreach ($comments as $c): ?>
                            <div class="comment" style="padding:8px 0;border-top:1px solid rgba(0,0,0,0.03);">
                                <strong><?= esc($c['full_name']) ?></strong>
                                <span class="time" style="color:#999; font-size:12px; margin-left:8px;"><?= date('M j, Y g:i A', strtotime($c['created_at'])) ?></span>
                                <div style="margin-top:6px;color:#333;"><?= esc($c['comment']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="padding: 0 16px 12px; display:flex; gap:8px; align-items:center;">
                        <input type="text" id="comment_input_<?= $post['post_id'] ?>" placeholder="Write a comment..." style="flex:1;padding:8px 12px;border-radius:8px;border:1px solid rgba(0,0,0,0.08);">
                        <button onclick="postComment(<?= $post['post_id'] ?>)" style="padding:8px 12px;border-radius:8px;background:#f3c547;border:none;cursor:pointer;">Comment</button>
                    </div>

                    <div class="post-footer">
                        <button id="like_btn_<?= $post['post_id'] ?>" class="post-footer-btn" onclick="likePost(<?= $post['post_id'] ?>, event)">
                            <i class="far fa-heart"></i> Like <span id="like_count_<?= $post['post_id'] ?>"><?= esc($post['likes_count']) ?></span>
                        </button>
                        <button id="comment_btn_<?= $post['post_id'] ?>" class="post-footer-btn">
                            <i class="far fa-comment"></i> Comment (<span id="comment_count_<?= $post['post_id'] ?>"><?= esc($post['comments_count']) ?></span>)
                        </button>
                        <button class="post-footer-btn">
                            <i class="far fa-share"></i> Share
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        // Enable submit button only when there's content
        document.getElementById('postContent').addEventListener('input', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = this.value.trim() === '';
        });

        function enableSubmitBtn() {
            const submitBtn = document.getElementById('submitBtn');
            const content = document.getElementById('postContent').value.trim();
            const imageInput = document.getElementById('image-input');
            
            submitBtn.disabled = content === '' && imageInput.files.length === 0;

            // Show preview
            if (imageInput.files.length > 0) {
                const file = imageInput.files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('preview');
                    preview.innerHTML = '<img src="' + e.target.result + '" style="max-width: 100%; max-height: 200px; border-radius: 8px; cursor: pointer;" onclick="document.getElementById(\'image-input\').click()">';
                };
                reader.readAsDataURL(file);
            }
        }

        function likePost(postId, evt) {
            const formData = new FormData();
            formData.append('post_id', postId);
            
            fetch('../../api/like_post.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const btn = evt?.target?.closest('.post-footer-btn');
                    if (!btn) return;
                    const countEl = document.getElementById('like_count_' + postId);
                    if (countEl) countEl.textContent = data.likes;
                    if (data.liked) {
                        btn.style.color = '#f3c547';
                        btn.innerHTML = '<i class="fas fa-heart"></i> Unlike <span id="like_count_' + postId + '">' + data.likes + '</span>';
                    } else {
                        btn.style.color = '#999';
                        btn.innerHTML = '<i class="far fa-heart"></i> Like <span id="like_count_' + postId + '">' + data.likes + '</span>';
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function postComment(postId) {
            const input = document.getElementById('comment_input_' + postId);
            const text = input.value.trim();
            if (!text) return;

            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('comment', text);

            fetch('../../api/comment_post.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const inputEl = document.getElementById('comment_input_' + postId);
                    const parent = inputEl.parentElement.previousElementSibling; // comments container
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = data.comment_html;
                    parent.appendChild(wrapper.firstChild);
                    inputEl.value = '';
                    const countEl = document.getElementById('comment_count_' + postId);
                    if (countEl) countEl.textContent = Number(countEl.textContent || 0) + 1;
                } else {
                    alert('Unable to post comment');
                }
            })
            .catch(err => console.error(err));
        }

        // Prevent form submission if disabled
        document.getElementById('postForm').addEventListener('submit', function(e) {
            if (document.getElementById('submitBtn').disabled) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
