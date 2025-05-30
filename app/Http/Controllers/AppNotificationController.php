<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;

class AppNotificationController extends Controller
{
    /**
     * Get unread notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $query = AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->where('read', false)
            ->latest();

        return $query->paginate($request->input('per_page', 15));
    }

    /**
     * Mark notifications as read
     */
    public function markAsRead(Request $request, AppNotification $notification)
    {
        $notification->update(['read' => true]);

        return response()->noContent();
    }
}
