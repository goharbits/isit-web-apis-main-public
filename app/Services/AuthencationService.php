<?php

namespace App\Services;

use App\Facades\GlobalHelper;
use App\Interfaces\AuthenticationInterface;
use App\Mail\OTPMail;
use App\Mail\ResetPasswordMail;
use App\Mail\UnderReviewMail;
use App\Mail\WelcomeMail;
use App\Models\Address;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;


class AuthencationService implements AuthenticationInterface
{
    public function __construct(
        private User $user,
        private Role $role,
        private Address $address
    ) {}
    public function login($data)
    {
        $user = $this->user->with('role')->where('email', $data['email'])->first();
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid credentials!'
            ];
        }

        $lastLogin = Carbon::parse($user->last_login_at);
        $now = Carbon::now();
        if ($user && $lastLogin->diffInDays($now) >= 30 && $user->role->name != "admin") {
            // $user->token()->revoke();
            $user->update([
                'email_verified_at' => null
            ]);

            $user = $user->toArray();
            $otp = GlobalHelper::generateOTP($user['id']);
            $user['otp'] = $otp;

            Mail::to($user['email'])->send(new OTPMail($user));
            return [
                'success' => false,
                'user_id' => $user['id'],
                'email' => $user['email'],
                'message' => 'Please verify your email address.'
            ];
        }
        $user = $this->user->with([
            'role',
            'images',
            'address',
            'subscription'
        ])->where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return [
                'success' => false,
                'message' => 'Invalid credentials!'
            ];
        }

        //check for email verification
        if (!$user->isEmailVerified()) {
            $user = $user->toArray();
            $otp = GlobalHelper::generateOTP($user['id']);
            $user['otp'] = $otp;

            Mail::to($user['email'])->send(new OTPMail($user));
            return [
                'success' => false,
                'user_id' => $user['id'],
                'email' => $user['email'],
                'message' => 'Please verify your email address.'
            ];
        }

        if ($user->status == 'Pending') {
            return [
                'success' => false,
                'message' => 'Your account is underreviews, for further information contact to Administrator.'
            ];
        }

        if ($user->status == 'Block') {
            return [
                'success' => false,
                'message' => 'Your account is blocked by Administrator.'
            ];
        }
        // if user is employee, check its cooperate if its subscription is expired then do not allow login.
        if ($user->role->name == 'employee') {
            if (!$user->corporate || $user->corporate->status !== 'Active') {
                return [
                    'success' => false,
                    'message' => 'Your corporate subscription has expired.You can not login'
                ];
            }
        }

        $token = $user->createToken('ISIT')->accessToken;
        $user = $user->toArray();
        $user['token'] = $token;
        return [
            'success' => true,
            'data' => $user
        ];
    }
    public function register($data)
    {
        $role = $this->role->where('name', $data['role'])->first();
        if (!$role) {
            return false;
        }

        $image = [];
        if ($data['role'] == "corporate") {
            array_push($image, [
                'path' => GlobalHelper::uploadFile($data['logo'], "uploads/logo"),
                'type' => 'logo'
            ]);
            array_push($image, [
                'path' => GlobalHelper::uploadFile($data['other'], "uploads/other"),
                'type' => 'other'
            ]);
        } else if ($data['role'] == "professional") {
            array_push($image, [
                'path' => GlobalHelper::uploadFile($data['profile'], "uploads/profile"),
                'type' => 'profile'
            ]);

            array_push($image, [
                'path' => GlobalHelper::uploadFile($data['certificate'], "uploads/certificate"),
                'type' => 'certificate'
            ]);

            array_push($image, [
                'path' => GlobalHelper::uploadFile($data['other'], "uploads/other"),
                'type' => 'other'
            ]);
        } else if ($data['role'] == "user") {
            $image[] = [
                'path' => GlobalHelper::uploadFile($data['profile'], "uploads/profile"),
                'type' => 'profile'
            ];
            $data['status'] = 'Active';
        } else {
        }

        $data['role_id'] = $role->id;
        if (isset($data['address'])) {
            $address = $this->address->create([
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'address' => $data['address']
            ]);
            $data['address_id'] = $address->id;
            $data['address'] = $address->toArray();
        }

        $data['last_login_at'] = now();

        $user = User::create($data);
        if (!$user) {
            return false;
        }

        $token = $user->createToken('ISIT')->accessToken;
        $user->images()->createMany($image);

        $user = $user->toArray();
        $user['token'] = $token;
        $user['role'] = $role->toArray();
        if (isset($data['address'])) {
            $user['address'] = $data['address'];
        }

        $otp = GlobalHelper::generateOTP($user['id']);
        $user['otp'] = $otp;

        Mail::to($user['email'])->send(new OTPMail($user));

        return $user;
    }

    public function forgetPassword($data)
    {
        $user = $this->user->where('email', $data['email'])->first();
        if (!$user) {
            return false;
        }
        $user = $user->toArray();
        $otp = GlobalHelper::generateOTP($user['id']);
        $user['otp'] = $otp;

        Mail::to($user['email'])->send(new OTPMail($user));

        return true;
    }

    public function verifyOTP($data)
    {
        $user = $this->user->where([
            'email' => $data['email']
        ])->first();

        if (!$user) {
            return false;
        }

        if (!GlobalHelper::isValidOTP($user->id, $data['otp'])) {
            return false;
        }

        $response = [
            'otp' => $data['otp'],
            'email' => $data['email']
        ];
        return $response;
    }

    public function resetPassword($data)
    {
        $user = $this->user->where([
            'email' => $data['email']
        ])->first();

        if (!$user) {
            return false;
        }

        if (!GlobalHelper::isValidOTP($user->id, $data['otp'])) {
            return false;
        }

        if (!GlobalHelper::OTPUsed($user->id, $data['otp'])) {
            return false;
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        Mail::to($user->email)->send(new ResetPasswordMail($user));

        return $user;
    }


    public function verifyEmail($id)
    {
        $user = $this->user->where('id', $id)->first();
        if (is_null($user)) {
            return false;
        }

        $user->update([
            'email_verified_at' => now(),
            'last_login_at' => now()
        ]);

        $user = $this->user->with('role')->where('id', $id)->first()->toArray();

        if ($user['role']['name'] == 'user') {
            GlobalHelper::startFreeTrial($user['id']);
        }

        Mail::to($user['email'])->send(new WelcomeMail($user));

        $role = $this->role->where('name', 'admin')->first();
        $admin = $this->user->where('role_id', $role->id)->first();

        Mail::to($admin->email)->send(new UnderReviewMail($user));

        return true;
    }


    public function getUser($id)
    {
        return $this->user
            ->with([
                'role',
                'images',
                'address',
                'subscription'
            ])
            ->where('id', $id)->first();
    }
}
