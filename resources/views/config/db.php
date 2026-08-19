<?php

if (!isset($_SESSION)) {
    if (function_exists('session_status')) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}

if (!function_exists('esc')) {
    function esc($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('db')) {
    function db()
    {
        if (class_exists('Illuminate\\Support\\Facades\\DB')) {
            return \Illuminate\Support\Facades\DB::connection()->getPdo();
        }

        return null;
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

if (!function_exists('require_role')) {
    function require_role($role)
    {
        if (!class_exists('Illuminate\\Support\\Facades\\Auth')) {
            return;
        }

        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user || ($user->role ?? null) !== $role) {
            http_response_code(403);
            echo 'Unauthorized. Requires ' . $role . ' role.';
            exit;
        }
    }
}

if (class_exists('Illuminate\\Support\\Facades\\Auth')) {
    $user = \Illuminate\Support\Facades\Auth::user();
    if ($user) {
        $_SESSION['user_id'] = $user->user_id ?? $user->id ?? null;
        $_SESSION['username'] = $user->username ?? $user->email ?? null;
        $_SESSION['full_name'] = $user->full_name ?? $user->name ?? null;
        $_SESSION['email'] = $user->email ?? null;
        $_SESSION['role'] = $user->role ?? null;
    }
}
