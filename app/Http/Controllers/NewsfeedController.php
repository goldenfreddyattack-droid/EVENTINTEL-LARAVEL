<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewsfeedController extends Controller
{
    public function __construct(private FirebaseService $firebase)
    {
    }

    public function index()
    {
        $allComments = collect($this->firebase->getNewsfeedComments());
        $posts = collect($this->firebase->getNewsfeedPosts())
            ->sortByDesc(fn (array $post) => $post['created_at'] ?? '')
            ->values();
        $likes = $this->firebase->getNewsfeedLikes();

        $userIds = $posts->pluck('user_id')
            ->merge($allComments->pluck('user_id'))
            ->filter()
            ->unique()
            ->values();
        $users = $userIds->isEmpty()
            ? collect()
            : DB::table('users')->whereIn('user_id', $userIds)->get()->keyBy('user_id');

        $posts = $posts->map(function (array $post) use ($allComments, $likes, $users) {
            $post['image_path'] = $post['image_path'] ?? null;
            $post['full_name'] = $this->displayName($users->get($post['user_id']));
            $post['likes_count'] = isset($likes[$post['post_id']]) && is_array($likes[$post['post_id']])
                ? count($likes[$post['post_id']])
                : 0;
            $post['comments_count'] = $allComments->where('post_id', $post['post_id'])->count();
            return (object) $post;
        });

        $comments = $allComments->groupBy('post_id')->map(fn ($postComments) => collect($postComments)->map(function (array $comment) use ($users) {
            $comment['full_name'] = $this->displayName($users->get($comment['user_id']));
            return (object) $comment;
        }));

        $likedPostIds = collect($likes)
            ->filter(fn ($usersForPost) => is_array($usersForPost) && array_key_exists((string) auth()->id(), $usersForPost))
            ->keys()
            ->all();

        return view('userui.newsfeed', compact('posts', 'comments', 'likedPostIds'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => ['nullable', 'string', 'max:5000', 'required_without:post_image'],
            'post_image' => ['nullable', 'image', 'max:5120'],
        ]);

        $imagePath = null;
        if ($request->hasFile('post_image')) {
            $directory = public_path('uploads/posts');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $filename = 'post_' . auth()->id() . '_' . Str::uuid() . '.' . $request->file('post_image')->extension();
            $request->file('post_image')->move($directory, $filename);
            $imagePath = 'posts/' . $filename;
        }

        if (! $this->firebase->createNewsfeedPost(auth()->id(), trim((string) ($validated['content'] ?? '')), $imagePath)) {
            return back()->withErrors(['content' => 'The post could not be saved right now.']);
        }

        return back()->with('success', 'Post shared successfully.');
    }

    public function like(Request $request)
    {
        $postId = (string) $request->validate(['post_id' => ['required', 'string']])['post_id'];
        $result = $this->firebase->toggleNewsfeedLike($postId, auth()->id());
        $likes = $this->firebase->getNewsfeedLikes();

        return response()->json([
            'liked' => $result['liked'],
            'likes' => isset($likes[$postId]) && is_array($likes[$postId]) ? count($likes[$postId]) : 0,
        ]);
    }

    public function comment(Request $request)
    {
        $validated = $request->validate([
            'post_id' => ['required', 'string'],
            'comment' => ['required', 'string', 'max:2000'],
        ]);
        $commentId = $this->firebase->createNewsfeedComment($validated['post_id'], auth()->id(), trim($validated['comment']));

        if (! $commentId) {
            return response()->json(['message' => 'Unable to save comment.'], 500);
        }

        return response()->json(['comment' => (object) [
            'comment_id' => $commentId,
            'post_id' => $validated['post_id'],
            'user_id' => auth()->id(),
            'full_name' => $this->displayName(auth()->user()),
            'comment' => trim($validated['comment']),
            'created_at' => now()->toIso8601String(),
        ]]);
    }

    private function displayName($user): string
    {
        return $user?->full_name ?: $user?->name ?: $user?->username ?: 'EventIntel member';
    }
}
