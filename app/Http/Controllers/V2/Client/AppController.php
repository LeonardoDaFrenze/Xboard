<?php

namespace App\Http\Controllers\V2\Client;

use App\Http\Controllers\Controller;
use App\Services\ServerService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

class AppController extends Controller
{
    public function getConfig(Request $request)
    {
        $config = [
            'app_info' => [
                'app_name' => admin_setting('app_name', 'XB Accelerator'), // Application Name
                'app_description' => admin_setting('app_description', 'Professional network acceleration service'), // Application Description
                'app_url' => admin_setting('app_url', 'https://app.example.com'), // Application official website URL
                'logo' => admin_setting('logo', 'https://example.com/logo.png'), // Application Logo URL
                'version' => admin_setting('app_version', '1.0.0'), // Application version number
            ],
            'features' => [
                'enable_register' => (bool) admin_setting('app_enable_register', true), // Whether to enable registration function
                'enable_invite_system' => (bool) admin_setting('app_enable_invite_system', true), // Whether to enable invitation system
                'enable_telegram_bot' => (bool) admin_setting('telegram_bot_enable', false), // Whether to enable Telegram Robot
                'enable_ticket_system' => (bool) admin_setting('app_enable_ticket_system', true), // Whether to enable ticket system
                'ticket_must_wait_reply' => (bool) admin_setting('ticket_must_wait_reply', 0), // Whether the ticket needs to wait for administrator reply before continuing to send messages
                'enable_commission_system' => (bool) admin_setting('app_enable_commission_system', true), // Whether to enable commission system
                'enable_traffic_log' => (bool) admin_setting('app_enable_traffic_log', true), // Whether to enable traffic log
                'enable_knowledge_base' => (bool) admin_setting('app_enable_knowledge_base', true), // Whether to enable knowledge base
                'enable_announcements' => (bool) admin_setting('app_enable_announcements', true), // Whether to enable announcement system
                'enable_auto_renewal' => (bool) admin_setting('app_enable_auto_renewal', false), // Whether to enable automatic renewal
                'enable_coupon_system' => (bool) admin_setting('app_enable_coupon_system', true), // Whether to enable coupon system
                'enable_speed_test' => (bool) admin_setting('app_enable_speed_test', true), // Whether to enable speed test function
                'enable_server_ping' => (bool) admin_setting('app_enable_server_ping', true), // Whether to enable server latency detection
            ],
            'ui_config' => [
                'theme' => [
                    'primary_color' => admin_setting('app_primary_color', '#00C851'), // Main color (Hexadecimal)
                    'secondary_color' => admin_setting('app_secondary_color', '#007E33'), // Accent color (Hexadecimal)
                    'accent_color' => admin_setting('app_accent_color', '#FF6B35'), // Highlight color (Hexadecimal)
                    'background_color' => admin_setting('app_background_color', '#F5F5F5'), // Background color (Hexadecimal)
                    'text_color' => admin_setting('app_text_color', '#333333'), // Text color (Hexadecimal)
                ],
                'home_screen' => [
                    'show_speed_test' => (bool) admin_setting('app_show_speed_test', true), // Whether to display speed test
                    'show_traffic_chart' => (bool) admin_setting('app_show_traffic_chart', true), // Whether to display traffic chart
                    'show_server_ping' => (bool) admin_setting('app_show_server_ping', true), // Whether to display server latency
                    'default_server_sort' => admin_setting('app_default_server_sort', 'ping'), // Default server sorting method
                    'show_connection_status' => (bool) admin_setting('app_show_connection_status', true), // Whether to display connection status
                ],
                'server_list' => [
                    'show_country_flags' => (bool) admin_setting('app_show_country_flags', true), // Whether to display country flag
                    'show_ping_values' => (bool) admin_setting('app_show_ping_values', true), // Whether to display latency value
                    'show_traffic_usage' => (bool) admin_setting('app_show_traffic_usage', true), // Whether to display traffic usage
                    'group_by_country' => (bool) admin_setting('app_group_by_country', false), // Whether to group by country
                    'show_server_status' => (bool) admin_setting('app_show_server_status', true), // Whether to display server status
                ],
            ],
            'business_rules' => [
                'min_password_length' => (int) admin_setting('app_min_password_length', 8), // Minimum password length
                'max_login_attempts' => (int) admin_setting('app_max_login_attempts', 5), // Maximum login attempt times
                'session_timeout_minutes' => (int) admin_setting('app_session_timeout_minutes', 30), // Session timeout time(Minutes)
                'auto_disconnect_after_minutes' => (int) admin_setting('app_auto_disconnect_after_minutes', 60), // Automatic disconnection time(Minutes)
                'max_concurrent_connections' => (int) admin_setting('app_max_concurrent_connections', 3), // Maximum concurrent connection number
                'traffic_warning_threshold' => (float) admin_setting('app_traffic_warning_threshold', 0.8), // Traffic warning threshold(0-1)
                'subscription_reminder_days' => admin_setting('app_subscription_reminder_days', [7, 3, 1]), // Subscription expiration reminder days
                'connection_timeout_seconds' => (int) admin_setting('app_connection_timeout_seconds', 10), // Connection timeout time(Seconds)
                'health_check_interval_seconds' => (int) admin_setting('app_health_check_interval_seconds', 30), // Health check interval(Seconds)
            ],
            'server_config' => [
                'default_kernel' => admin_setting('app_default_kernel', 'clash'), // Default kernel (clash/singbox)
                'auto_select_fastest' => (bool) admin_setting('app_auto_select_fastest', true), // Whether to automatically select the fastest server
                'fallback_servers' => admin_setting('app_fallback_servers', ['server1', 'server2']), // Backup server list
                'enable_auto_switch' => (bool) admin_setting('app_enable_auto_switch', true), // Whether to enable automatic switching
                'switch_threshold_ms' => (int) admin_setting('app_switch_threshold_ms', 1000), // Switching threshold(Milliseconds)
            ],
            'security_config' => [
                'tos_url' => admin_setting('tos_url', 'https://example.com/tos'), // Terms of service URL
                'privacy_policy_url' => admin_setting('app_privacy_policy_url', 'https://example.com/privacy'), // Privacy policy URL
                'is_email_verify' => (int) admin_setting('email_verify', 1), // Whether to enable email verification (0/1)
                'is_invite_force' => (int) admin_setting('invite_force', 0), // Whether to enforce invitation code (0/1)
                'email_whitelist_suffix' => (int) admin_setting('email_whitelist_suffix', 0), // Email whitelist suffix (0/1)
                'is_captcha' => (int) admin_setting('captcha_enable', 1), // Whether to enable captcha (0/1)
                'captcha_type' => admin_setting('captcha_type', 'recaptcha'), // Captcha type (recaptcha/turnstile)
                'recaptcha_site_key' => admin_setting('recaptcha_site_key', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'), // reCAPTCHA Site key
                'recaptcha_v3_site_key' => admin_setting('recaptcha_v3_site_key', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'), // reCAPTCHA v3 Site key
                'recaptcha_v3_score_threshold' => (float) admin_setting('recaptcha_v3_score_threshold', 0.5), // reCAPTCHA v3 Score threshold
                'turnstile_site_key' => admin_setting('turnstile_site_key', '0x4AAAAAAAABkMYinukE8nzUg'), // Turnstile Site key
            ],
            'payment_config' => [
                'currency' => admin_setting('currency', 'CNY'), // Currency type
                'currency_symbol' => admin_setting('currency_symbol', '¥'), // Currency symbol
                'withdraw_methods' => admin_setting('app_withdraw_methods', ['alipay', 'wechat', 'bank']), // Withdrawal method list
                'min_withdraw_amount' => (int) admin_setting('app_min_withdraw_amount', 100), // Minimum withdrawal amount(Cents)
                'withdraw_fee_rate' => (float) admin_setting('app_withdraw_fee_rate', 0.01), // Withdrawal fee rate
            ],
            'notification_config' => [
                'enable_push_notifications' => (bool) admin_setting('app_enable_push_notifications', true), // Whether to enable push notification
                'enable_email_notifications' => (bool) admin_setting('app_enable_email_notifications', true), // Whether to enable email notification
                'enable_sms_notifications' => (bool) admin_setting('app_enable_sms_notifications', false), // Whether to enable SMS notification
                'notification_schedule' => [
                    'traffic_warning' => (bool) admin_setting('app_notification_traffic_warning', true), // Traffic warning notification
                    'subscription_expiry' => (bool) admin_setting('app_notification_subscription_expiry', true), // Subscription expiration notification
                    'server_maintenance' => (bool) admin_setting('app_notification_server_maintenance', true), // Server maintenance notification
                    'promotional_offers' => (bool) admin_setting('app_notification_promotional_offers', false), // Promotion discount notification
                ],
            ],
            'cache_config' => [
                'config_cache_duration' => (int) admin_setting('app_config_cache_duration', 3600), // Configuration cache duration(Seconds)
                'server_list_cache_duration' => (int) admin_setting('app_server_list_cache_duration', 1800), // Server list cache duration(Seconds)
                'user_info_cache_duration' => (int) admin_setting('app_user_info_cache_duration', 900), // User information cache duration(Seconds)
            ],
            'last_updated' => time(), // Last update timestamp
        ];
        $config['config_hash'] = md5(json_encode($config)); // Configuration hash value(Used for verification)

        $config = $config ?? [];
        return response()->json(['data' => $config]);
    }

    public function getVersion(Request $request)
    {
        if (
            strpos($request->header('user-agent'), 'tidalab/4.0.0') !== false
            || strpos($request->header('user-agent'), 'tunnelab/4.0.0') !== false
        ) {
            if (strpos($request->header('user-agent'), 'Win64') !== false) {
                $data = [
                    'version' => admin_setting('windows_version'),
                    'download_url' => admin_setting('windows_download_url')
                ];
            } else {
                $data = [
                    'version' => admin_setting('macos_version'),
                    'download_url' => admin_setting('macos_download_url')
                ];
            }
        } else {
            $data = [
                'windows_version' => admin_setting('windows_version'),
                'windows_download_url' => admin_setting('windows_download_url'),
                'macos_version' => admin_setting('macos_version'),
                'macos_download_url' => admin_setting('macos_download_url'),
                'android_version' => admin_setting('android_version'),
                'android_download_url' => admin_setting('android_download_url')
            ];
        }
        return $this->success($data);
    }
}
