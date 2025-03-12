<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index($userId)
    {
        try {
            $notifications = $this->notificationService->getNotifications($userId);
            return $this->success($notifications);
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }

    public function show($id, $userId)
    {
        try {
            $notification = $this->notificationService->getNotificationDetail($id, $userId);
            if (!$notification) {
                return $this->error('No notification found.');
            }
            return $this->success($notification);
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }
}
