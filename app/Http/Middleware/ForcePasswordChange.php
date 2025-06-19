<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (auth()->check()) {
            $user = auth()->user();
            
            // Check if user needs to change password
            if ($user->must_change_password === true) {
                // Allow access to password change routes and logout
                $allowedRoutes = [
                    'password.force_change',
                    'password.force_change.update',
                    'logout',
                    'login' // Allow going back to login if needed
                ];
                
                if (!in_array($request->route()->getName(), $allowedRoutes)) {
                    return redirect()->route('password.force_change')
                        ->with('error', 'You must change your password before continuing.');
                }
            }
        }
    
        return $next($request);
    }
}
