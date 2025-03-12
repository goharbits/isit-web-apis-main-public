<?php

namespace App\Services;

use App\Facades\GlobalHelper;
use App\Interfaces\SubscriptionInterface;
use App\Models\Plan;
use App\Models\Role;
use App\Models\UserSubscriptionHistory;
use App\Models\Subscription as ModelsSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Stripe\Customer;
use Stripe\Price;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Checkout\Session;
use Stripe\Subscription;
use Carbon\Carbon;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionSuccessEmail;

class SubscriptionService implements SubscriptionInterface
{
    public $user;
    public $role;
    public $plan;
    public $subscription;
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->user = new User();
        $this->role = new Role();
        $this->plan = new Plan();
        $this->subscription = new ModelsSubscription();
    }

    public function retrieveProducts($roleName)
    {
        $role = $this->role->where('name', $roleName)->first();
        if (!$role) {
            return false;
        }

        $plan = $this->plan->where('role_id', $role->id)->first();

        $stripe = new StripeClient(config('services.stripe.secret'));
        $product = $stripe->products->retrieve($plan->plan_id, []);

        $prices = Price::all(['product' => $product->id]);
        $free = Plan::where('type', 'free')->first()->toArray();


        if (isset($prices->data) || is_array($prices->data)) {
            $pricesArray = $prices->data;
            // Rearrange the array using usort
            usort($pricesArray, function ($a, $b) {
                // Compare the interval values
                if ($a->recurring->interval == 'month' && $b->recurring->interval == 'year') {
                    return -1; // Move 'month' before 'year'
                } elseif ($a->recurring->interval == 'year' && $b->recurring->interval == 'month') {
                    return 1; // Move 'year' after 'month'
                }
                return 0; // Keep the same if they are equal
            });
            $prices->data = $pricesArray;
        }


        return [
            'product' => $product,
            'prices' => $prices->data,
            'free' => $free
        ];
    }

    public function subscribe($data)
    {
        $user = $this->user->where(['id' => $data['user_id']])->first();

        if ($user->stripe_customer_id) {
            $customerId = $user->stripe_customer_id;
        } else {
            $customer = Customer::create([
                'name' => $user->name,
                'email' => $user->email
            ]);
            $customerId = $customer->id;
        }


        // dd($customerId);

        $customerId = 'cus_RY6aWzzgKRAhVX';

        $stripe = new StripeClient(config('services.stripe.secret'));
        $subscribed = $stripe->subscriptions->create([
            'customer' => $customerId,
            'items' => [['price' => $data['price_id']]],
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
            'expand' => ['latest_invoice.payment_intent'],
        ]);

        dd($subscribed);
    }


    public function createSubscriptionSession($data)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $price_id = '';
        $type = $data['type'];
        $url = $data['url'];
        $end_date = '';
        $start_date = Carbon::now();

        if ($type == 'month') {
            $end_date = Carbon::now()->addMonth();
        } else if ($type == 'year') {
            $end_date = Carbon::now()->addYear();
        }

        $user = $this->user->where(['id' => $data['user_id']])->first();

        try {
            $checkoutSession = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $data['price_id'],
                    'quantity' => 1,
                ]],
                'customer_email' => $user->email,
                'mode' => 'subscription',
                'success_url' => config('app.url') . $url . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.url') . $url . '?session_id={CHECKOUT_SESSION_ID}',
            ]);

            if ($checkoutSession) {
                UserSubscriptionHistory::create([
                    'user_id' => $user->id,
                    'session_id' => $checkoutSession->id,
                    'subscription_frequency' => $type,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'session_verified_status' => 0
                ]);

                return $checkoutSession->id;
            }

            return false;
        } catch (\Exception $e) {
            Log::log('Error Payment', $e->getMessage());
            return false;
        }
    }


    public function checkSubscriptionStatus($data)
    {
        $session_id = $data['session_id'];
        try {

            $sub_history = UserSubscriptionHistory::where('session_id', $session_id)
                ->where('session_verified_status', 0)->with('user.subscription')->first();

            if (!$sub_history) {
                return false;
            }

            $user = $sub_history->user;

            if ($sub_history->subscription_frequency == 'year') {
                $description = 'Yearly Subscription';
                $type = 'year';
            } else {
                $description = 'Monthly Subscription';
                $type = 'month';
            }

            $session = Session::retrieve($sub_history->session_id);

            if ($session->payment_status == 'paid') {

                $stripeCustomerId = $session->customer;
                $subscription = Subscription::retrieve($session->subscription);

                $priceId = $subscription->items->data[0]->plan->id;
                $plan = Plan::where('plan_id', $subscription->plan->product)->first();
                $start = GlobalHelper::convertUnixToDatetime($subscription->current_period_start);
                $end = GlobalHelper::convertUnixToDatetime($subscription->current_period_end);
                $stripePlanId = $subscription->plan->product;
                GlobalHelper::startSubscription($type, $user->id, $plan->id, $session->subscription, $start, $end, $description, $stripePlanId);

                $sub_history->update([
                    'status' => $session->payment_status,
                    'session_verified_status' => 1
                ]);

                $user->update([
                    'status' => 'Active',
                ]);


                // try {
                //     // Send the email to the user
                //     Mail::to($user->email)->send(new SubscriptionSuccessEmail($user));
                //     //Log::info("Reminder email sent to: {$user->email}");
                // } catch (\Exception $e) {
                //     // Log::error(" Failed to send Reminder email to: {$user->email}");
                // }

                $user = $this->user->with([
                    'role',
                    'images',
                    'address',
                    'subscription'
                ])->where('id', $user->id)->first();
                return $user;
            } else {

                $sub_history->update([
                    'status' => $session->payment_status,
                    'session_verified_status' => 1
                ]);

                return false;
            }
        } catch (ApiErrorException $e) {
            return false;
        }
    }



    public function cancelSubscription($data)
    {
        $stripe = new StripeClient(config('services.stripe.secret'));
        $subscription = $stripe->subscriptions->cancel($data['subscription_id'], []);
        $subscriptionId = $subscription->id;

        $subscribed = $this->subscription->where('stripe_subscription_id', $subscriptionId)->with('user')->first();

        // $endDate = GlobalHelper::convertUnixToDatetime($subscription->canceled_at);
        if ($subscribed) {
            $subscribed->update(['status' => 'cancelled']);
            if (Carbon::parse($subscribed->end_date)->isPast()) {
                $subscribed->user->update([
                    'status' => 'Inactive',
                ]);
            }
        }
        $user = $this->user->with([
            'role',
            'images',
            'address',
            'subscription'
        ])->where('id', $subscribed->user->id)->first();
        return $user;
    }
}
