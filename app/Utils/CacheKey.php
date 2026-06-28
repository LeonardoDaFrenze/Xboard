<?php

namespace App\Utils;

class CacheKey
{
// Core cache key definitions
    const CORE_KEYS = [
        'EMAIL_VERIFY_CODE' => 'Email verification code',
        'LAST_SEND_EMAIL_VERIFY_TIMESTAMP' => 'Last time email verification code was sent',
        'TEMP_TOKEN' => 'Temporary token',
        'LAST_SEND_EMAIL_REMIND_TRAFFIC' => 'Last time flow email reminder was sent',
        'SCHEDULE_LAST_CHECK_AT' => 'Last check time of scheduled task',
        'REGISTER_IP_RATE_LIMIT' => 'Registration frequency limit',
        'LAST_SEND_LOGIN_WITH_MAIL_LINK_TIMESTAMP' => 'Last time login link was sent',
        'PASSWORD_ERROR_LIMIT' => 'Password error count limit',
        'USER_SESSIONS' => 'User session',
        'FORGET_REQUEST_LIMIT' => 'Password recovery attempt limit'
    ];

// Allowed cache key patterns (supports wildcards)
    const ALLOWED_PATTERNS = [
        'SERVER_*_ONLINE_USER',        // Online users on a node
        'MULTI_SERVER_*_ONLINE_USER',  // Online users across multiple servers
        'SERVER_*_LAST_CHECK_AT',      // Last check time of the node
        'SERVER_*_LAST_PUSH_AT',       // Last push time of the node
        'SERVER_*_LOAD_STATUS',        // Node load status
        'SERVER_*_LAST_LOAD_AT',       // Last submission time of node load data
        'SERVER_*_METRICS',            // Node metric data
        'USER_ONLINE_CONN_*_*',        // Number of user connections online (Specific node type_ID)
    ];

    /**
     * Generate cache key
     */
    public static function get(string $key, mixed $uniqueValue = null): string
    {
// Check if it is a core key
        if (array_key_exists($key, self::CORE_KEYS)) {
            return $uniqueValue ? $key . '_' . $uniqueValue : $key;
        }

// Check if it matches the allowed pattern
        if (self::matchesPattern($key)) {
            return $uniqueValue ? $key . '_' . $uniqueValue : $key;
        }

// Log warnings in development environment, allow through in production environment
        if (app()->environment('local', 'development')) {
            logger()->warning("Unknown cache key used: {$key}");
        }

        return $uniqueValue ? $key . '_' . $uniqueValue : $key;
    }

    /**
     * Check if the key name matches the allowed pattern
     */
    private static function matchesPattern(string $key): bool
    {
        foreach (self::ALLOWED_PATTERNS as $pattern) {
            $regex = '/^' . str_replace('*', '[A-Za-z0-9_]+', $pattern) . '$/';
            if (preg_match($regex, $key)) {
                return true;
            }
        }
        return false;
    }
}
