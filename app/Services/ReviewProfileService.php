<?php

namespace App\Services;

use App\Interfaces\ReviewProfileInterface;
use App\Models\Role;
use App\Models\User;
use App\Facades\GlobalHelper;


class ReviewProfileService implements ReviewProfileInterface
{
    public function __construct(
        private User $user,
        private Role $role
    ) {}

    public function getReviewProfiles()
    {
        $role = $this->role->whereIn('name', ['professional', 'corporate'])->get();
        $user = $this->user->with(['role', 'images', 'services', 'schedules', 'address'])
            ->whereIn('role_id', $role->pluck('id'))
            ->whereNotNull('email_verified_at')
            ->where('status', 'Pending')
            ->paginate(10);
        return $user;
    }
    public function getReviewProfile($id)
    {
        $role = $this->role->whereIn('name', ['professional', 'corporate'])->get();
        $user = $this->user->with(['role', 'images', 'services', 'schedules', 'address'])
            ->whereIn('role_id', $role->pluck('id'))
            ->whereNotNull('email_verified_at')
            ->where([
                'id' => $id,
                'status' => 'Pending'
            ])
            ->first();
        return $user;
    }
    public function updateReviewProfile($data)
    {
        $role = $this->role->whereIn('name', ['professional', 'corporate'])->get();
        $user = $this->user
            ->whereIn('role_id', $role->pluck('id'))
            ->whereNotNull('email_verified_at')
            ->where([
                'id' => $data['id'],
                'status' => 'Pending'
            ])
            ->first();

        if (is_null($user)) {
            return false;
        }

        GlobalHelper::startFreeTrial($data['id']);

        $user->update([
            'status' => ucwords($data['status'])
        ]);




        $user = $this->user->with(['role', 'images', 'services', 'schedules', 'address'])
            ->whereIn('role_id', $role->pluck('id'))
            ->where('id', $data['id'])
            ->first();

        return $user;
    }
}
