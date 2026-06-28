<?php

namespace Tests\Unit\Models;

use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a payment method can be created successfully.
     *
     * @return void
     */
    public function test_payment_creation_is_successful(): void
    {
        $payment = Payment::factory()->create([
            'uuid' => 'uuid-string-1234',
            'payment' => 'Stripe',
            'name' => 'Credit Card',
            'icon' => 'stripe-icon',
            'enable' => 1,
        ]);

        $this->assertDatabaseHas('v2_payment', [
            'id' => $payment->id,
            'payment' => 'Stripe',
            'name' => 'Credit Card',
            'enable' => 1,
        ]);

        $this->assertInstanceOf(Payment::class, $payment);
    }

    /**
     * Test that payment attributes casts are applied.
     *
     * @return void
     */
    public function test_payment_casts_are_applied(): void
    {
        $payment = Payment::factory()->create([
            'enable' => 1,
        ]);

        $this->assertIsBool($payment->enable);
        $this->assertTrue($payment->enable);
    }

    /**
     * Test that a payment method can be disabled.
     *
     * @return void
     */
    public function test_payment_can_be_disabled(): void
    {
        $payment = Payment::factory()->create([
            'enable' => 1,
        ]);

        $payment->update([
            'enable' => 0,
        ]);

        $this->assertDatabaseHas('v2_payment', [
            'id' => $payment->id,
            'enable' => 0,
        ]);
    }

    /**
     * Test that a payment method can be deleted.
     *
     * @return void
     */
    public function test_payment_can_be_deleted(): void
    {
        $payment = Payment::factory()->create();
        $paymentId = $payment->id;

        $payment->delete();

        $this->assertDatabaseMissing('v2_payment', [
            'id' => $paymentId,
        ]);
    }
}
