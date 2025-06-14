<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminUserSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserViewSwitchController extends Controller
{
    protected $sessionService;

    public function __construct(AdminUserSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function switchToAdmin()
    {
        try {
            if (session('admin_as_user')) {
                $this->sessionService->endUserSession();
                return redirect()->route('admin.dashboard');
            }

            return redirect()->back();
        } catch (\Exception $e) {
            Log::error('Error switching to admin view: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to switch to admin view. Please try again.');
        }
    }

    public function switchToUser()
    {
        try {
            if (Auth::guard('admin')->check() && !session('admin_as_user')) {
                $this->sessionService->createUserSession(Auth::guard('admin')->user());
                return redirect()->route('dashboard');
            }

            return redirect()->back();
        } catch (\Exception $e) {
            Log::error('Error switching to user view: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to switch to user view. Please try again.');
        }
    }
}
