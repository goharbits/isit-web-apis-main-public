<?php

namespace App\Http\Controllers\Feedback;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\FeedbackRequest;
use App\Http\Requests\Feedback\FeedbackReplyRequest;
use App\Services\FeedbackService;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function __construct(
        private FeedbackService $feedbackService
    ) {}


    public function giveFeedback(FeedbackRequest $request)
    {

        try {
            $feedback = $this->feedbackService->addFeedback($request->all());
            if (!$feedback) {
                return $this->error('Invalid payload.');
            }
            return $this->success($feedback);
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }



    public function giveFeedbackReply(FeedbackReplyRequest $request)
    {

        try {
            $feedback = $this->feedbackService->addFeedbackReply($request->all());
            if (!$feedback) {
                return $this->error('Invalid payload.');
            }
            return $this->success($feedback);
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }

    public function getFeedback($id)
    {

        try {
            $feedback = $this->feedbackService->getFeedback($id);
            return $this->success($feedback);
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }

    public function setFeedback()
    {
        try {
            // $feedback = $this->feedbackService->getFeedback($id);
            // return $this->success($feedback);
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }
}
