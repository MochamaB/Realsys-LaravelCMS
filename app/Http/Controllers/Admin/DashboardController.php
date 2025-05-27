<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Page;
use App\Models\User;
use App\Models\Widget;
use App\Models\WidgetType;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get admin user from session
        $admin = Auth::guard('admin')->user();
        
        // Get pages statistics
        $pages = Page::withTrashed();
        $pagesStats = [
            'total' => $pages->count(),
            'published' => $pages->where('status', 'published')->count(),
            'draft' => $pages->where('status', 'draft')->count(),
            'recent' => Page::latest()->take(5)->get()
        ];

        // Get users statistics
        $users = User::withTrashed();
        $usersStats = [
            'total' => $users->count(),
            'active' => $users->where('status', 'active')->count(),
            'recent' => User::latest()->take(5)->get()
        ];

        // Get widgets statistics
        $widgets = Widget::withTrashed();
        $widgetsStats = [
            'total' => $widgets->count(),
            'published' => $widgets->where('status', 'published')->count(),
            'draft' => $widgets->where('status', 'draft')->count(),
            'recent' => Widget::latest()->take(5)->get()
        ];

        // Get widget types statistics
        $widgetTypes = WidgetType::withTrashed();
        $widgetTypesStats = [
            'total' => $widgetTypes->count(),
            'active' => $widgetTypes->where('is_active', true)->count()
        ];

        // Combine all stats
        $stats = [
            'pages' => $pagesStats,
            'users' => $usersStats,
            'widgets' => $widgetsStats,
            'widgetTypes' => $widgetTypesStats,
            'recent_pages' => $pagesStats['recent'],
            'recent_users' => $usersStats['recent']
        ];

        return view('admin.dashboard.dashboard', [
            'admin' => $admin,
            'stats' => $stats
        ]);
    }
}
