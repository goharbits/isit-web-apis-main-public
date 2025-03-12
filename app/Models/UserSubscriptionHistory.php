<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscriptionHistory extends Model
{
    use HasFactory;
    protected $table = 'user_subscriptions';
    protected $fillable = ['user_id','subscription_id','session_verified_status','session_id','subscription_frequency','start_date','end_date', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
