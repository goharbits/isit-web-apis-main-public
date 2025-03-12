<?php

namespace App\Interfaces;

interface NotificationInterface
{
    public function getNotifications($userId);
    public function getNotificationDetail($id, $userId);
}
