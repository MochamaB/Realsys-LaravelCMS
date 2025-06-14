<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AdminUserSessionService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAsUser
{
    protected $sessionService;

    public function __construct(AdminUserSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Check if admin is authenticated and not already viewing as user
        if (Auth::guard('admin')->check() && !Auth::guard('web')->check() && !session('admin_as_user')) {
            $this->sessionService->createUserSession(Auth::guard('admin')->user());
        }

        return $next($request);
    }
}