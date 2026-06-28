<?php

namespace Tests\Unit\Services;

use App\Services\CaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class CaptchaServiceTest extends TestCase
{
    use RefreshDatabase;

    private CaptchaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CaptchaService::class);
        admin_setting(['captcha_enable' => 0]);
    }

    public function test_verify_returns_true_when_disabled(): void
    {
        $request = Request::create('/test', 'GET');

        [$passed, $error] = $this->service->verify($request);

        $this->assertTrue($passed);
        $this->assertNull($error);
    }

    public function test_verify_turnstile_missing_token(): void
    {
        admin_setting([
            'captcha_enable' => 1,
            'captcha_type' => 'turnstile',
        ]);

        $request = Request::create('/test', 'GET');

        [$passed, $error] = $this->service->verify($request);

        $this->assertFalse($passed);
        $this->assertEquals(400, $error[0]);
    }

    public function test_verify_recaptcha_missing_data(): void
    {
        admin_setting([
            'captcha_enable' => 1,
            'captcha_type' => 'recaptcha',
        ]);

        $request = Request::create('/test', 'GET');

        [$passed, $error] = $this->service->verify($request);

        $this->assertFalse($passed);
        $this->assertEquals(400, $error[0]);
    }

    public function test_verify_recaptcha_v3_missing_token(): void
    {
        admin_setting([
            'captcha_enable' => 1,
            'captcha_type' => 'recaptcha-v3',
        ]);

        $request = Request::create('/test', 'GET');

        [$passed, $error] = $this->service->verify($request);

        $this->assertFalse($passed);
        $this->assertEquals(400, $error[0]);
    }

    public function test_verify_invalid_type(): void
    {
        admin_setting([
            'captcha_enable' => 1,
            'captcha_type' => 'invalid_type',
        ]);

        $request = Request::create('/test', 'GET');

        [$passed, $error] = $this->service->verify($request);

        $this->assertFalse($passed);
        $this->assertEquals(400, $error[0]);
    }
}
