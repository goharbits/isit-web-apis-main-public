<?php

namespace App\Interfaces;

interface ReviewProfileInterface
{
    public function getReviewProfiles();

    public function getReviewProfile($id);

    public function updateReviewProfile($data);
}
