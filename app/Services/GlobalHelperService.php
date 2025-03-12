<?php

namespace App\Services;

use App\Events\NotificationEvent;
use App\Mail\SubscriptionMail;
use App\Models\Address;
use App\Models\Conversation;
use App\Models\Notification;
use App\Models\OTP;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class GlobalHelperService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function uploadFile($file, $directory = 'uploads')
    {
        if (!$file->isValid()) {
            return false;
        }

        $uniqueName = uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $uniqueName, 'public');

        return $path;
    }

    public function generateOTP($user_id)
    {
        $otp = rand(1000, 9999);
        $currentTime = Carbon::now();
        $expires_at = $currentTime->addMinutes(5);
        OTP::create([
            'user_id' => $user_id,
            'otp' => $otp,
            'expires_at' => $expires_at,
        ]);
        return $otp;
    }

    public function isValidOTP($user_id, $otp)
    {
        $query = OTP::where([
            'user_id' => $user_id,
            'otp' => $otp
        ])
            ->where('expires_at', '>', Carbon::now())
            ->whereNot('is_used', true)
            ->first();

        if (!$query) {
            return false;
        }
        return true;
    }

    public function OTPUsed($user_id, $otp)
    {
        $query = OTP::where([
            'otp' => $otp,
            'user_id' => $user_id
        ])->first();

        if (!$query) {
            return false;
        }

        $query->delete();
        return true;
    }

    public function addAddress($data)
    {
        $query = Address::create([
            'longitude' => $data['longitude'],
            'latitude' => $data['latitude'],
            'address' => $data['address'],
        ]);
        if (!$query) {
            return false;
        }
        return $query->id;
    }


    public function hasRole($id, $name)
    {
        $user = User::find($id);
        if (!$user) {
            return false;
        }
        return $user->hasRole($name);
    }

    public function makeNotification($requestId, $receiver, $title = '', $description = '', $sender)
    {
        $notification = Notification::create([
            'request_id' => $requestId,
            'user_id' => $receiver,
            'title' =>  $title,
            'description' => $description,
            'sender_id' => $sender
        ]);

        $notification = Notification::with(['user.images', 'request.customer.images', 'request.employee.images', 'sender.images', 'sender.role'])->find($notification->id);

        event(new NotificationEvent($notification));
    }


    public function isAlreadyInConversation($senderId, $receiverId)
    {
        $existingConversation = Conversation::where(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $senderId)
                ->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $receiverId)
                ->where('receiver_id', $senderId);
        })->first();

        if ($existingConversation) {
            return true;
        }
        return false;
    }

    public function generateRandomId($prefix = 'prod_', $length = 14)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $prefix . $randomString;
    }

    public function startFreeTrial($userId)
    {
        $startDate = Carbon::now();
        $endDate = $startDate->copy()->addDays(30);
        $plan = Plan::where('type', 'free')->first();
        Subscription::create([
            'plan_id' => $plan->id,
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'name' => 'Free Trial',
            'description' => 'Free Trial of 30 Days',
            'type' => 'free'
        ]);
        return true;
    }

    public function startSubscription($type, $userId, $planId, $stripeSubscriptionId, $startDate, $endDate, $description, $stripePlanId)
    {
        $subscription =  Subscription::updateOrCreate([
            'user_id' => $userId,
        ], [
            'type' => $type,
            'name' => $description,
            'description' => $description,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'plan_id' => $planId,
            'stripe_subscription_id' => $stripeSubscriptionId,
            'status' => 'active',
            'stripe_plan_id' => $stripePlanId

        ]);

        $subscription = Subscription::with('user')->where('user_id', $subscription->user_id)->first();

        Mail::to($subscription->user->email)->send(new SubscriptionMail($subscription));

        return $subscription;
    }


    public function cancelSubscription($user)
    {

        $subscription = Subscription::with('user')
            ->where('id', $user->id)
            ->first();

        if ($subscription) {
            if (Carbon::parse($subscription->end_date)->isPast()) {
                $subscription->update(['status' => 'inactive']);
                $subscription->user->update(['status' => 'Inactive']);
            } else {
                $subscription->update(['status' => 'cancelled']);
            }
        }
        return $subscription;
    }

    public function convertUnixToDatetime($unixTimestamp)
    {
        return date('Y-m-d H:i:s', $unixTimestamp);
    }
}
