<?php

namespace Plugin\Stripe;

use App\Services\Plugin\AbstractPlugin;
use App\Contracts\PaymentInterface;
use App\Exceptions\ApiException;

class Plugin extends AbstractPlugin implements PaymentInterface
{
    public function boot(): void
    {
        $this->filter('available_payment_methods', function ($methods) {
            if ($this->getConfig('enabled', true)) {
                $methods['Stripe'] = [
                    'name' => $this->getConfig('display_name', 'Stripe'),
                    'icon' => $this->getConfig('icon', '💳'),
                    'plugin_code' => $this->getPluginCode(),
                    'type' => 'plugin',
                ];
            }
            return $methods;
        });
    }

    public function form(): array
    {
        return [
            'stripe_sk_key' => [
                'label' => 'Secret Key',
                'type' => 'string',
                'required' => true,
                'description' => 'Stripe secret key — starts with sk_live_ or sk_test_',
            ],
            'stripe_webhook_key' => [
                'label' => 'Webhook Signing Secret',
                'type' => 'string',
                'required' => true,
                'description' => 'Webhook signing secret from your Stripe Dashboard — starts with whsec_',
            ],
            'currency' => [
                'label' => 'Currency',
                'type' => 'string',
                'required' => false,
                'description' => 'Three-letter ISO currency code (e.g. usd, eur, gbp). Default: usd',
            ],
            'display_name' => [
                'label' => 'Display Name',
                'type' => 'string',
                'required' => false,
                'description' => 'Payment method name shown to users. Default: Stripe',
            ],
        ];
    }

    public function pay($order): array
    {
        \Stripe\Stripe::setApiKey($this->getConfig('stripe_sk_key'));

        $currency = strtolower($this->getConfig('currency', 'usd'));

        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => 'Subscription',
                            'description' => 'Order: ' . $order['trade_no'],
                        ],
                        'unit_amount' => (int) $order['total_amount'],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'client_reference_id' => $order['trade_no'],
                'success_url' => $order['return_url'],
                'cancel_url' => $order['return_url'],
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new ApiException('Stripe error: ' . $e->getMessage());
        }

        return [
            'type' => 1,
            'data' => $session->url,
        ];
    }

    public function notify($params): array|bool
    {
        $payload   = trim(request()->getContent());
        $sigHeader = request()->header('Stripe-Signature');
        $secret    = $this->getConfig('stripe_webhook_key');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            throw new ApiException('Stripe webhook signature verification failed', 400);
        } catch (\UnexpectedValueException $e) {
            throw new ApiException('Invalid Stripe webhook payload', 400);
        }

        if ($event->type !== 'checkout.session.completed') {
            return false;
        }

        $session = $event->data->object;

        if ($session->payment_status !== 'paid') {
            return false;
        }

        return [
            'trade_no'    => $session->client_reference_id,
            'callback_no' => $session->id,
        ];
    }
}
