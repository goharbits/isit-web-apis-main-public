<?php

namespace App\Interfaces;

interface ScheduleInterface
{
    public function addSchedule($data);
    public function getSchedule($user_id);
    public function deleteSchedule($data);
}
