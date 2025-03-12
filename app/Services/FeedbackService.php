<?php

namespace App\Services;

use App\Facades\GlobalHelper;
use App\Interfaces\FeedbackInterface;
use App\Models\Feedback;
use App\Models\Request;
use App\Models\Service;
use App\Models\User;


class FeedbackService implements FeedbackInterface
{
    public function __construct(
        private Feedback $feedback,
        private User $user,
        private Service $service,
        private Request $request
    ) {}

    public function addFeedback($data)
    {
        $user = $this->user->where('id', $data['user_id'])->first();
        if (!$user) {
            return false;
        }

        $request = $this->request->with(['user.role'])->where('id', $data['request_id'])->first();

        if (!$request) {
            return false;
        }

        $service = $this->service->where('id', $data['service_id'])->first();
        if (!$service) {
            return false;
        }

        $feedback = $this->feedback->create($data);

        $request->update([
            'status' => 'Completed'
        ]);

        $sender = $request->customer_id;
        $receiver = $request->user_id;
        $title = "Service feedback";
        $description = "You got a feedback on your $service->name service. Check it out!";

        GlobalHelper::makeNotification($data['request_id'], $receiver, $title, $description, $sender);

        return $feedback;
    }

    public function addFeedbackReply($data)
    {

        $user = $this->user->where('id', $data['user_id'])->first();
        if (!$user) {
            return false;
        }

        $request = $this->request->with(['user.role'])->where('id', $data['request_id'])->first();

        if (!$request) {
            return false;
        }

        $service = $this->service->where('id', $data['service_id'])->first();
        if (!$service) {
            return false;
        }
        $feedbackId = $data['feedback_id'];

        $originalFeedback = $this->feedback->findOrFail($feedbackId);
        if (!$originalFeedback) {
            return false;
        }

        $data['parent_id'] = $originalFeedback->id;
        $data['user_id'] = $data['customer_id'];
        $data['customer_id'] = $data['user_id'];

        $feedback = $this->feedback->create($data);

      
        $request->update([
            'status' => 'Completed'
        ]);

        $sender = $request->customer_id;
        $receiver = $request->user_id;
        $title = "Service feedback Reply";
        $description = "You got a feedback on your $service->name service. Check it out!";

        GlobalHelper::makeNotification($data['request_id'], $receiver, $title, $description, $sender);

        return $feedback;
    }
    public function getFeedback($user_id)
    {
        $feedback = $this->feedback->with([
            'user',
            'customer.images',
            'service',
            'request',
            'replies'
        ])->where('user_id', $user_id)->orderBy('created_at', 'desc')->paginate(10);
        return $feedback;
    }

    public function getSingleFeedback($id)
    {
        $feedback = $this->feedback->with([
            'user',
            'customer.images',
            'service',
            'request'
        ])->where('id', $id)->first();
        return $feedback;
    }
}
