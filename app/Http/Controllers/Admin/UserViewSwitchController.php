<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminUserSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserViewSwitchController extends Controller
{
    protected $sessionService;

    public function __construct(AdminUserSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function switchToAdmin()
    {
        if (session('admin_as_user')) {
            $this->sessionService->endUserSession();
            return redirect()->route('admin.dashboard');
        }

        return redirect()->back();
    }

    public function switchToUser()
    {
        if (Auth::guard('admin')->check() && !session('admin_as_user')) {
            $this->sessionService->createUserSession(Auth::guard('admin')->user());
            return redirect()->route('dashboard');
        }

        return redirect()->back();
    }
}
