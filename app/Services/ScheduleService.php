<?php

namespace App\Services;

use App\Interfaces\ScheduleInterface;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\User;

class ScheduleService implements ScheduleInterface
{
    public function __construct(
        private Schedule $schedule,
        private User $user,
        private Role $role
    ) {}

    public function getSchedule($id)
    {
        $schedule = $this->schedule->where('user_id', $id)
            ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->get();
        if (!$schedule) {
            return false;
        }
        return $schedule;
    }

    public function addSchedule($data)
    {
        // dd($data['user_id']);
        $user = $this->user->where('id', $data['user_id'])->first();

        if (!$user) {
            return false;
        }

        $role = $this->role->where(['id' => $user->role_id])->first();

        $allowedRoles = ['professional', 'employee'];
        if (!in_array($role->name, $allowedRoles)) {
            return false;
        }

        $schedule =   $this->schedule->create([
            'day' => $data['day'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'user_id' => $data['user_id']
        ]);
        return $schedule;
    }


    public function updateSchedule($data)
    {
        $user = $this->user->where('id', $data['user_id'])->first();
        if (!$user) {
            return false;
        }

        $schedule = $this->schedule->where('id', $data['schedule_id'])->first();

        if (!$schedule) {
            return false;
        }

        $schedule->update([
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time']
        ]);

        return $schedule;
    }

    public function deleteSchedule($id)
    {
        $schedule = $this->schedule->where('id', $id)->first();

        if (!$schedule) {
            return false;
        }

        $schedule->delete();
        return true;
    }
}
