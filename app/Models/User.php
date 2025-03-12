<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'ss_number',
        'about',
        'phone_no',
        'ein_number',
        'parent_id',
        'address_id',
        'registration_number',
        'tax_id_number',
        'work_permit_id',
        'status',
        'email_verified_at',
        'stripe_customer_id',
        'last_login_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function role()
    {
        return $this->belongsTo(Role::class)->select('id', 'name');
    }

    public function corporateServices()
    {
        return $this->hasMany(Service::class, 'corporate_id');
    }

    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    public function address()
    {
        return $this->belongsTo(Address::class)->select('id', 'address', 'longitude', 'latitude');
    }

    public function corporate()
    {
        return $this->belongsTo(User::class, 'parent_id')->select('id', 'name', 'email', 'status');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function isEmailVerified()
    {
        return !is_null($this->email_verified_at);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function userSubscription()
    {
        return $this->hasOne(UserSubscription::class)
            ->where('status', 'paid')
            ->orderBy('id', 'desc');
    }
}
