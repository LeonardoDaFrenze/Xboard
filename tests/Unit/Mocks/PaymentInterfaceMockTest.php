<?php

namespace Tests\Unit\Mocks;

use Tests\TestCase;
use App\Models\Order;
use App\Contracts\PaymentInterface;
use Mockery\MockInterface;

use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentInterfaceMockTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_interface_mock()
    {
        $mock = $this->mock(PaymentInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('pay')
                 ->once()
                 ->andReturn([
                     'type' => 1,
                     'data' => 'http://example.com/pay/12345'
                 ]);
                 
            $mock->shouldReceive('notify')
                 ->andReturn(true);
        });

        $order = Order::factory()->make(['total_amount' => 1500]);

        $response = $mock->pay($order->toArray());

        $this->assertIsArray($response);
        $this->assertArrayHasKey('type', $response);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals(1, $response['type']);
        $this->assertEquals('http://example.com/pay/12345', $response['data']);
        $this->assertTrue($mock->notify([]));
    }
}
