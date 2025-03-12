<?php

use App\Events\MessageEvent;
use App\Events\NotificationEvent;
use App\Facades\GlobalHelper;
use App\Http\Controllers\Chat\ChatController;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Support\Facades\Route;
use Stripe\Customer;
use Stripe\Price;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Subscription;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/get-notification', [ChatController::class, 'sendMessage']);

Route::get('/product', function () {
    $productId = 'prod_RY3qNzLyhNjzb5';
    Stripe::setApiKey(config('services.stripe.secret'));
    $stripe = new StripeClient(config('services.stripe.secret'));
    $product = $stripe->products->retrieve($productId, []);

    $prices = Price::all(['product' => $product->id]);

    dd($prices);
});
