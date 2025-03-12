<?php

namespace App\Services;

use App\Interfaces\NotificationInterface;
use App\Models\Notification;

class NotificationService implements NotificationInterface
{
    public function __construct(
        private Notification $notification
    ) {}

    public function getNotifications($userId)
    {
        $notifications = $this->notification->with(['user.images', 'sender.images', 'sender.role', 'request.customer.images', 'request.employee.schedules', 'request.employee.images'])->where('user_id', $userId)->orderBy('id', 'desc')->paginate(10);
        return $notifications;
    }

    public function getNotificationDetail($id, $userId)
    {
        $notification = $this->notification->with(['user.images', 'sender.role', 'sender.images', 'sender.services', 'sender.address', 'sender.schedules', 'request.user.schedules', 'request.customer.images', 'request.customer.address', 'request.service', 'request.feedback', 'request.schedule', 'request.employee.schedules', 'request.employee.images', 'sender_feedback'])->where([
            'id' => $id,
            'user_id' => $userId
        ])->first();

        if (!$notification) {
            return false;
        }

        return $notification;
    }
}
