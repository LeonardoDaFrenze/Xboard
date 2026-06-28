<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        try {
            admin_setting(['app_url' => 'http://localhost']);
        } catch (\Throwable $e) {
            // Ignore database exceptions for unit tests that do not run database migrations
        }
    }

    /**
     * Get the URI for admin endpoints dynamically.
     *
     * @param string $path
     * @return string
     */
    protected function getAdminUri(string $path = ''): string
    {
        $securePath = admin_setting('secure_path', admin_setting('frontend_admin_path', hash('crc32b', config('app.key'))));
        return '/api/v2/' . $securePath . '/' . ltrim($path, '/');
    }

    /**
     * Set the currently logged in user for the application under sanctum guard.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param array $abilities
     * @param string|null $guard
     * @return $this
     */
    public function actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, $abilities = [], $guard = 'sanctum')
    {
        \Laravel\Sanctum\Sanctum::actingAs($user, $abilities, $guard ?? 'sanctum');
        return $this;
    }
}
