<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->latest()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function show(UserNotification $notification)
    {
        // Ensure the user can only view their own notifications
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$notification->is_read) {
            $notification->markAsRead();
        }

        return view('notifications.show', compact('notification'));
    }

    public function markAsRead(Request $request)
    {
        $notificationId = $request->input('id');
        $notification = UserNotification::where('id', $notificationId)
                                      ->where('user_id', Auth::id())
                                      ->first();

        if ($notification && !$notification->is_read) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications()->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true, 'message' => 'All notifications marked as read.']);
    }

    public function destroy(UserNotification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->delete();

        return back()->with('success', 'Notification deleted successfully.');
    }
    
    public function getUnreadCount()
    {
        return response()->json(['count' => Auth::user()->unreadNotifications->count()]);
    }
}

