<?php

/**
 * Bootstrap file for legacy Supplier PHP pages
 * Provides compatibility layer between Laravel and old PHP code
 */

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\FirebaseService;

// Session compatibility
if (!function_exists('esc')) {
    function esc($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

// Database function
if (!function_exists('db')) {
    function db()
    {
        return DB::connection()->getPdo();
    }
}

if (!function_exists('table_exists')) {
    function table_exists($tableName)
    {
        $pdo = db();
        if (!$pdo) {
            return false;
        }

        $stmt = $pdo->prepare(
            'SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1'
        );
        $stmt->execute([$tableName]);

        return (bool) $stmt->fetchColumn();
    }
}

// Role checking function
if (!function_exists('require_role')) {
    function require_role($role)
    {
        $user = Auth::user();

        if (!$user || $user->role !== $role) {
            abort(403, 'Unauthorized. Requires ' . $role . ' role.');
        }
    }
}

// Firebase helpers
if (!function_exists('firebase')) {
    function firebase(): FirebaseService
    {
        return app(FirebaseService::class);
    }
}

if (!function_exists('firebase_save_message')) {
    function firebase_save_message($eventId, $senderId, $receiverId, $message, $senderName = 'User')
    {
        return firebase()->saveMessage($eventId, $senderId, $receiverId, $message, $senderName);
    }
}

if (!function_exists('firebase_get_messages_for_thread')) {
    function firebase_get_messages_for_thread($eventId, $userId, $otherUserId = null)
    {
        return firebase()->getMessages($eventId, $userId, $otherUserId);
    }
}

if (!function_exists('mark_messages_read')) {
    function mark_messages_read($pdo, $eventId, $userId, $otherUserId = null)
    {
        return firebase()->markMessagesAsRead($eventId, $userId, $otherUserId, $userId);
    }
}

if (!function_exists('count_unread_for')) {
    function count_unread_for($pdo, $userId, $eventId, $otherUserId = null)
    {
        return firebase()->getUnreadCount($userId, $eventId, $otherUserId);
    }
}

if (!function_exists('total_unread_for_user')) {
    function total_unread_for_user($pdo, $userId)
    {
        return 0; // Placeholder - can be enhanced to count all unread across all threads
    }
}

// Session setup for legacy pages
if (!isset($_SESSION)) {
    $_SESSION = [];
}

$user = Auth::user();
if ($user) {
    $_SESSION['user_id'] = $user->user_id;
    $_SESSION['username'] = $user->username;
    $_SESSION['full_name'] = $user->full_name;
    $_SESSION['email'] = $user->email;
    $_SESSION['role'] = $user->role;
}
