<?php

namespace App\Interfaces;

interface AuthenticationInterface
{
    public function login($data);
    public function register($data);
    public function forgetPassword($data);
    public function verifyOTP($data);
    public function resetPassword($data);
}
