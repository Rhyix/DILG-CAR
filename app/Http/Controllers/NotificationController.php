<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationController extends Controller
{
    protected function getQuery()
    {
        if (Auth::guard('admin')->check()) {
            $adminId = Auth::guard('admin')->id();
            return Notification::where('notifiable_type', 'App\Models\Admin')
                ->where(function ($q) use ($adminId) {
                    $q->whereNull('notifiable_id')
                      ->orWhere('notifiable_id', $adminId);
                });
        } elseif (Auth::check()) {
            return Notification::where('notifiable_type', 'App\Models\User')
                ->where('notifiable_id', Auth::id());
        }
        return null;
    }

    // Fetch count of unread notifications
    public function unreadCount()
    {
        $query = $this->getQuery();
        if (!$query)
            return response()->json(['count' => 0]);
        $count = $query->whereNull('read_at')->count();

        return response()->json(['count' => $count]);
    }

    // Index method
    public function index()
    {
        $query = $this->getQuery();
        if ($query) {
            $notifications = $query->latest()->paginate(10);
        } else {
            $notifications = new LengthAwarePaginator(
                [],
                0,
                10,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }
        return view('notifications.index', compact('notifications'));
    }

    // Fetch latest notifications
    public function fetch()
    {
        $query = $this->getQuery();
        if (!$query)
            return response()->json(['notifications' => []]);

        $notifications = $query->latest()->paginate(10);

        // Return standard notifications collection
        $payload = $notifications->toArray();
        $payload['notifications'] = $payload['data'] ?? [];
        return response()->json($payload);
    }

    // Mark all as read
    public function markAll()
    {
        $query = $this->getQuery();
        if (!$query)
            return response()->json(['success' => false], 403);

        $query->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    // Mark individual notification as read
    public function markAsRead($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        // Security Check
        $authorized = false;
        if (Auth::guard('admin')->check()) {
            if (
                $notification->notifiable_type === 'App\Models\Admin' &&
                ($notification->notifiable_id == Auth::guard('admin')->id() || $notification->notifiable_id === null)
            ) {
                $authorized = true;
            }
        } elseif (Auth::check()) {
            if ($notification->notifiable_type === 'App\Models\User' && $notification->notifiable_id == Auth::id()) {
                $authorized = true;
            }
        }

        if ($authorized) {
            $notification->update(['read_at' => now()]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    // Clear all notifications
    public function cleanup()
    {
        $query = $this->getQuery();
        if (!$query)
            return response()->json(['success' => false], 403);

        $query->delete();

        return response()->json(['success' => true]);
    }
}
