<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin User Session Settings
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for managing admin-to-user
    | session transitions and security measures.
    |
    */

    // Session timeout in minutes for admin viewing as user
    'session_timeout' => env('ADMIN_USER_SESSION_TIMEOUT', 60),

    // Whether to require re-authentication when switching back to admin
    'require_reauth' => env('ADMIN_USER_REQUIRE_REAUTH', true),

    // Maximum number of concurrent user sessions per admin
    'max_concurrent_sessions' => env('ADMIN_USER_MAX_SESSIONS', 1),

    // Whether to log all session switches
    'log_session_switches' => env('ADMIN_USER_LOG_SWITCHES', true),

    // Security settings
    'security' => [
        // Whether to validate IP address on session switch
        'validate_ip' => env('ADMIN_USER_VALIDATE_IP', true),

        // Whether to validate user agent on session switch
        'validate_user_agent' => env('ADMIN_USER_VALIDATE_USER_AGENT', true),

        // Allowed IP addresses for session switching (empty array means all IPs allowed)
        'allowed_ips' => explode(',', env('ADMIN_USER_ALLOWED_IPS', '')),

        // Whether to automatically end user sessions when admin logs out
        'end_on_admin_logout' => env('ADMIN_USER_END_ON_LOGOUT', true),
    ],
]; 