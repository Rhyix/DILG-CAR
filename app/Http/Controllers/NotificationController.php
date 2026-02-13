<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;
use App\Models\User;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = User::query()->findOrFail(Auth::id());
        $notifications = $user->notifications()
            ->latest()
            ->paginate(10);

        if ($request->wantsJson()) {
            return response()->json($notifications);
        }

        return view('notifications.index', compact('notifications'));
    }

    public function unreadCount()
    {
        $user = User::query()->findOrFail(Auth::id());
        $count = $user->unreadNotifications()->count();
        return response()->json(['count' => $count]);
    }

    public function fetch(Request $request)
    {
        $page = (int) $request->get('page', 1);
        $per = (int) $request->get('per_page', 10);
        $user = User::query()->findOrFail(Auth::id());
        $notifications = $user->notifications()
            ->latest()
            ->paginate($per, ['*'], 'page', $page);
        return response()->json($notifications);
    }

    public function markAsRead(string $id)
    {
        $user = User::query()->findOrFail(Auth::id());
        $notification = $user->notifications()->where('id', $id)->first();
        if (!$notification) {
            return response()->json(['message' => 'Not found'], 404);
        }
        if (!$notification->read_at) {
            $notification->markAsRead();
        }
        return response()->json(['ok' => true]);
    }

    public function markAll()
    {
        $user = User::query()->findOrFail(Auth::id());
        $user->unreadNotifications->markAsRead();
        return response()->json(['ok' => true]);
    }

    public function cleanup(Request $request)
    {
        $days = (int) ($request->get('days') ?? config('notifications.retention_days', 90));
        $cutoff = Carbon::now()->subDays($days);
        DatabaseNotification::where('created_at', '<', $cutoff)->delete();
        return response()->json(['ok' => true, 'deleted_before' => $cutoff->toISOString()]);
    }
}
