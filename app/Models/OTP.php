<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    protected $table = "otps";

    protected $fillable = ['user_id', 'otp', 'is_used', 'expires_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
