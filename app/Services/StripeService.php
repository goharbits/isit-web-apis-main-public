<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Stripe\Price;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function retrieveProduct($productId)
    {
        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
            $product = $stripe->products->retrieve($productId, []);
            $prices = Price::all(['product' => $product->id]);

            return [
                'product' => $product,
                'prices' => $prices
            ];
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }
}
