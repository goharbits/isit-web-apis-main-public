<?php

namespace App\Http\Controllers\Schedule;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\ScheduleRequest;
use App\Http\Requests\Schedule\UpdateScheduleRequest;
use App\Services\ScheduleService;
use Illuminate\Http\Request;


class ScheduleController extends Controller
{
    public function __construct(
        private ScheduleService $scheduleService
    ) {}


    public function index($id)
    {
        try {
            $schedule = $this->scheduleService->getSchedule($id);
            if (!$schedule) {
                return $this->error('No schedule set yet.');
            }
            return $this->success($schedule);
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }

    public function store(ScheduleRequest $request)
    {
        try {
            $schedule = $this->scheduleService->addSchedule($request->all());
            if (!$schedule) {
                return $this->error('Sorry, not a valid user to perform this task!');
            }
            return $this->success($schedule, 'Scheduled updated.');
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }

    public function update(UpdateScheduleRequest $request)
    {
        try {
            $schedule = $this->scheduleService->updateSchedule($request->all());
            if (!$schedule) {
                return $this->error('Sorry, not a valid user to perform this task!');
            }
            return $this->success($schedule, 'Scheduled updated.');
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $schedule = $this->scheduleService->deleteSchedule($id);
            if (!$schedule) {
                return $this->error('Sorry, schedule not found!');
            }
            return $this->success([], 'Schedule deleted!');
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }
}
