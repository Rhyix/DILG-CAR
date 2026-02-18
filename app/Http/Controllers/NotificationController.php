<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use Spatie\Activitylog\Models\Activity;

class NotificationController extends Controller
{
    private function getQuery() {
        if (Auth::guard('admin')->check()) {
            return Notification::where('notifiable_type', 'App\Models\Admin')
                ->where(function($q) {
                    $q->where('notifiable_id', Auth::guard('admin')->id())
                      ->orWhereNull('notifiable_id');
                })
                ->where(function($q) {
                    $q->where('data->category', 'document_verification')
                      ->orWhere('data->category', 'exam_lifecycle')
                      ->orWhere('data->category', 'exam_questions');
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
        if (!$query) return response()->json(['count' => 0]);
        $count = $query->whereNull('read_at')->count();
        if ($count === 0 && Auth::guard('admin')->check()) {
            $count = Activity::latest()->take(10)->count();
        }
        return response()->json(['count' => $count]);
    }

    // Index method
    public function index()
    {
        return view('notifications.index');
    }

    // Fetch latest notifications
    public function fetch()
    {
        $query = $this->getQuery();
        if (!$query) return response()->json(['notifications' => []]);

        $notifications = $query->latest()->paginate(10);
        if (Auth::guard('admin')->check()) {
            $activities = Activity::latest()->take(10)->get();
            $mapped = $activities->map(function ($a) {
                $props = $a->properties ?? collect();
                $section = $props['section'] ?? ucfirst((string) ($a->event ?? 'Activity'));
                $actor = optional($a->causer)->name ?? optional($a->causer)->username ?? 'Unknown';
                $msg = trim((string) ($a->description ?? 'performed an action'));
                $msg = rtrim($msg, ". \t\n\r\0\x0B");
                $message = $actor . ' ' . $msg . '.';
                $link = null;
                $userId = $props['user_id'] ?? null;
                $vacancyId = $props['vacancy_id'] ?? null;
                if ($userId && $vacancyId) {
                    $link = route('admin.applicant_status', ['user_id' => $userId, 'vacancy_id' => $vacancyId]);
                } elseif ($vacancyId && in_array($section, ['Exam Management', 'Application List', 'Job Vacancy'])) {
                    $link = route('admin.manage_exam', ['vacancy_id' => $vacancyId]);
                } elseif ($section === 'System Users Management') {
                    $link = route('admin_account_management');
                }
                return [
                    'id' => 'activity_' . $a->id,
                    'type' => 'info',
                    'data' => [
                        'title' => $section,
                        'message' => $message,
                        'link' => $link,
                    ],
                    'read_at' => null,
                    'created_at' => $a->created_at,
                ];
            });
            // Merge stored notifications with activity-derived ones
            $combined = collect($notifications->items())->concat($mapped)->take(10)->values();
            return response()->json([
                'notifications' => $combined,
                'data' => $combined,
                'current_page' => 1,
                'next_page_url' => null
            ]);
        }

        $payload = $notifications->toArray();
        $payload['notifications'] = $payload['data'] ?? [];
        return response()->json($payload);
    }

    // Mark all as read
    public function markAll()
    {
        $query = $this->getQuery();
        if (!$query) return response()->json(['success' => false], 403);

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
            if ($notification->notifiable_type === 'App\Models\Admin' && 
               ($notification->notifiable_id == Auth::guard('admin')->id() || $notification->notifiable_id === null)) {
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
        if (!$query) return response()->json(['success' => false], 403);

        $query->delete();

        return response()->json(['success' => true]);
    }
}
