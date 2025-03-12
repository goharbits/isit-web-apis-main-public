<?php

namespace App\Services;

use App\Interfaces\AvailServiceInterface;
use App\Models\Request;

class AvailService implements AvailServiceInterface
{
    public function __construct(
        private Request $request
    ) {}

    public function getAvailServices($userId)
    {
        $availServices = $this->request->with(['user.images', 'user.role', 'employee.images', 'service', 'feedback'])->where('user_id', $userId)
            ->orWhere('customer_id', $userId)
            ->whereIn('status', ['Accepted', 'Completed'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $availServices;
    }
}
