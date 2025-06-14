<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AdminUserSessionService;
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
        if (auth()->guard('admin')->check() && !auth()->guard('web')->check()) {
            $this->sessionService->createUserSession(auth()->guard('admin')->user());
        }

        return $next($request);
    }
}