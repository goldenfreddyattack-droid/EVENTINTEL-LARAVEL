<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected string $databaseUrl;
    protected string $apiKey;
    protected array $messageCache = [];
    protected ?int $lastSavedMessageId = null;

    public function __construct()
    {
        $this->databaseUrl = config('services.firebase.database_url', 'https://eventintel-72be0-default-rtdb.firebaseio.com');
        $this->apiKey = config('services.firebase.api_key', '');
    }

    /**
     * Save a message to Firebase
     */
    public function saveMessage(int $eventId, int $senderId, ?int $receiverId, string $message, string $senderName = 'User', ?int $messageId = null): bool
    {
        try {
            $threadKey = $this->getThreadKey($eventId, $senderId, $receiverId);
            $this->lastSavedMessageId = $messageId ?? (int) round(microtime(true) * 1000);
            $messageData = [
                'message_id' => $this->lastSavedMessageId,
                'sender_id' => $senderId,
                'sender_name' => $senderName,
                'receiver_id' => $receiverId,
                'event_id' => $eventId,
                'message' => $message,
                'timestamp' => now()->timestamp,
                'read_by' => [],
            ];

            $response = Http::post(
                "{$this->databaseUrl}/messages/{$threadKey}/{$eventId}.json",
                $messageData
            );

            if (!$response->successful()) {
                Log::error('Firebase message save rejected', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Firebase message save failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function lastSavedMessageId(): ?int
    {
        return $this->lastSavedMessageId;
    }

    /**
     * Get messages for a thread
     */
    public function getMessages(int $eventId, int $userId, ?int $otherUserId = null): array
    {
        $cacheKey = $eventId . ':' . $userId . ':' . ($otherUserId ?? '');
        if (array_key_exists($cacheKey, $this->messageCache)) {
            return $this->messageCache[$cacheKey];
        }

        try {
            $threadKey = $this->getThreadKey($eventId, $userId, $otherUserId);
            $response = Http::get(
                "{$this->databaseUrl}/messages/{$threadKey}/{$eventId}.json"
            );

            if ($response->successful() && $response->json()) {
                $data = $response->json();
                if (isset($data['sender_id'])) {
                    $data = [$data];
                } else {
                    $data = array_values($data);
                }

                usort($data, function (array $left, array $right): int {
                    $leftId = (float) ($left['message_id'] ?? $left['timestamp'] ?? 0);
                    $rightId = (float) ($right['message_id'] ?? $right['timestamp'] ?? 0);
                    return $leftId <=> $rightId;
                });

                return $this->messageCache[$cacheKey] = $data;
            }
            return $this->messageCache[$cacheKey] = [];
        } catch (\Exception $e) {
            Log::error('Firebase message fetch failed', ['error' => $e->getMessage()]);
            return $this->messageCache[$cacheKey] = [];
        }
    }

    /**
     * Mark messages as read
     */
    public function markMessagesAsRead(int $eventId, int $userId, ?int $otherUserId, ?int $readByUserId = null): bool
    {
        try {
            $threadKey = $this->getThreadKey($eventId, $userId, $otherUserId);
            $readByUserId = $readByUserId ?? auth()->id() ?? $userId;

            $response = Http::patch(
                "{$this->databaseUrl}/messages/{$threadKey}/{$eventId}/read_by/{$readByUserId}.json",
                ['status' => true]
            );

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Firebase mark read failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get unread message count for a user
     */
    public function getUnreadCount(int $userId, int $eventId, ?int $otherUserId = null): int
    {
        try {
            $messages = $this->getMessages($eventId, $userId, $otherUserId);
            $unreadCount = 0;

            foreach ($messages as $message) {
                if (is_array($message) && isset($message['read_by'])) {
                    if (!isset($message['read_by'][$userId])) {
                        $unreadCount++;
                    }
                }
            }

            return $unreadCount;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Generate a unique thread key
     */
    protected function getThreadKey(int $eventId, int $userId, ?int $otherUserId = null): string
    {
        $ids = [$userId, $otherUserId];
        sort($ids);
        return 'event_' . $eventId . '_' . implode('_', array_filter($ids));
    }

    /**
     * Health check for Firebase connection
     */
    public function healthCheck(): bool
    {
        try {
            $response = Http::get("{$this->databaseUrl}/.json");
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Firebase health check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
