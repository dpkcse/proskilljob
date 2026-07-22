<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $admin = auth('admin')->user();
        $canAccess = $admin && ($admin->hasRole('superadmin') || userCan('activity-log.view'));
        abort_if(! $canAccess, 403);

        $logs = ActivityLog::query();
        if ($request->filled('category')) {
            $category = $request->category;
            $logs->where('route_name', 'like', $category.'%');
        }

        $logs = $logs->latest()->paginate(20)->withQueryString();

        $categories = [
            'candidate' => __('candidate'),
            'company' => __('company'),
            'job' => __('jobs'),
            'settings' => __('settings'),
            'activity-log' => __('activity_log'),
        ];

        return view('backend.activity-log.index', compact('logs', 'categories'));
    }

    public function unreadCount()
    {
        $admin = auth('admin')->user();
        $canAccess = $admin && ($admin->hasRole('superadmin') || userCan('activity-log.view'));
        abort_if(! $canAccess, 403);

        return response()->json([
            'count' => ActivityLog::where('created_at', '>=', now()->subMinutes(15))->count(),
        ]);
    }
}
