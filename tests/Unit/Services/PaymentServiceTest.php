<?php

namespace Tests\Unit\Services;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_service_with_temp_method(): void
    {
        $service = new PaymentService('temp');

        $this->assertNotNull($service);
    }

    public function test_get_all_payment_method_names_returns_array(): void
    {
        $methods = PaymentService::getAllPaymentMethodNames();

        $this->assertIsArray($methods);
    }

    public function test_get_available_payment_methods_returns_array(): void
    {
        $service = new PaymentService('temp');

        $methods = $service->getAvailablePaymentMethods();

        $this->assertIsArray($methods);
    }
}
