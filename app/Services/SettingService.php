<?php

namespace App\Services;

use App\Interfaces\SettingInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SettingService  implements SettingInterface
{
    public function __construct(
        private User $user
    ) {}
    public function resetPassword($data)
    {
        $user = $this->user->where('email', $data['email'])->first();
        if (!$user) {
            return false;
        }

        if (!Hash::check($data['old_password'], $user->password)) {
            return false;
        }

        $user->update([
            'password' => Hash::make($data['password'])
        ]);

        return $user;
    }
}
