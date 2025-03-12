<?php

namespace App\Interfaces;

interface FeedbackInterface
{
    public function getFeedback($user_id);
    public function addFeedbackReply($data);
    public function addFeedback($data);
    public function getSingleFeedback($id);
}
