<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'name',
        'description',
        'stripe_subscription_id',
        'start_date',
        'end_date',
        'status',
        'user_id',
        'plan_id',
        'stripe_plan_id',
        'type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
