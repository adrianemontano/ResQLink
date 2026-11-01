<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class NotificationController extends Controller
{
    /**
     * Get recent notifications and unread count for the dispatcher.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->take(10)->get();

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $notifications->map(fn ($n) => [
                'id' => $n->id,
                'data' => $n->data,
                'read_at' => $n->read_at,
                'created_at' => $n->created_at->diffForHumans(),
            ]),
        ]);
    }

    /**
     * Lightweight polling endpoint checking for new emergency alerts.
     */
    public function poll(Request $request): JsonResponse
    {
        $user = $request->user();
        $since = $request->query('since');

        $query = $user->unreadNotifications();
        if ($since) {
            try {
                $since = Carbon::parse($since)->timezone(config('app.timezone'))->format('Y-m-d H:i:s');
            } catch (\Throwable) {
                throw ValidationException::withMessages(['since' => 'The polling timestamp is invalid.']);
            }
            $query->where('created_at', '>', $since);
        }

        $newAlerts = $query->latest()->take(5)->get();

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'new_alerts' => $newAlerts->map(fn ($n) => [
                'id' => $n->id,
                'data' => $n->data,
                'created_at' => $n->created_at->diffForHumans(),
            ]),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, string $notification): JsonResponse
    {
        $record = $request->user()->notifications()->where('id', $notification)->first();
        if ($record) {
            $record->markAsRead();
        }

        return response()->json([
            'success' => true,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }
}
