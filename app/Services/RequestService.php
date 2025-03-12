<?php

namespace App\Services;

use App\Facades\GlobalHelper;
use App\Interfaces\RequestInterface;
use App\Models\Conversation;
use App\Models\Request;
use App\Models\Role;
use Illuminate\Support\Facades\Log;

class RequestService implements RequestInterface
{
    public function __construct(
        private Request $request,
        private Conversation $conversation,
        private Role $role
    ) {}

    public function makeRequest($data)
    {
        $request = $this->request->create($data);
        if (!$request) {
            return false;
        }

        $request = $this->request->with(['user.images', 'customer', 'employee', 'service', 'schedule'])->where('id', $request->id)->first();
        $title = $request->customer->name . " Request Job";
        $description = $request->customer->name . " has requested for job.";

        $role = $this->role->where('name', 'employee')->first();

        if (isset($data['employee_id']) && $role->id == $data['employee_id']) {
            $description .= " for " . $request->employee->name;
        }

        $sender = $request->customer->id;
        $receiver = $request->user->id;
        GlobalHelper::makeNotification($request->id, $receiver, $title, $description, $sender);
        return $request;
    }
    public function updateStatusRequest($data)
    {
        // $request = $this->request->where(['id' => $data['id'], 'user_id' => $data['user_id']])->first();
        $request = $this->request->where(['id' => $data['id']])->first();
        $rescheduleStatus = $request->status;
        $role = $this->role->where('name', 'corporate')->first();
        $corporate = $this->role->where('name', 'user')->first();
        if (!$request) {
            return false;
        }

        $isSchedule = false;
        if ($request->status === "Reschedule" && $data['status'] == "Rejected") {
            $isSchedule = true;
        }

        $reason = "";
        if (isset($data['reason'])) {
            $reason = $data['reason'];
        }

        $request->update([
            'status' => $data['status'],
            'reason' => $reason
        ]);


        $request = $this->request->with(['user.images', 'customer', 'service', 'schedule', 'employee'])->where('id', $request->id)->first();
        if ($request->status == 'Accepted') {

            if ($request->user->role_id == $role->id && $data['user_id'] == $request->user->id) {
                $data['title'] = $request->employee->name . ' Request Accepted';
                $data['description'] = $request->employee->name . ' has accepted your request Start chat now!';
            } elseif ($data['user_id'] == $request->customer->id) {
                $data['title'] = $request->customer->name . ' Request Accepted';
                $data['description'] = $request->customer->name . ' has accepted your request Start chat now!';
            } else {
                $data['title'] = $request->user->name . ' Request Accepted';
                $data['description'] = $request->user->name . ' has accepted your request Start chat now!';
            }

            if ($request->user->role_id == $role->id) {
                if (!GlobalHelper::isAlreadyInConversation($request->employee_id, $request->customer_id)) {
                    $this->conversation->create([
                        'sender_id' => $request->employee_id,
                        'receiver_id' => $request->customer_id
                    ]);
                }
            } else {
                if (!GlobalHelper::isAlreadyInConversation($request->user_id, $request->customer_id)) {
                    $this->conversation->create([
                        'sender_id' => $request->user_id,
                        'receiver_id' => $request->customer_id
                    ]);
                }
            }

            if (isset($request->employee->name) && $rescheduleStatus == "Reschedule") {
                $sender = $request->customer_id;
                $receiver = $request->user_id;
            } else {
                $sender = $request->user_id;
                $receiver = $request->customer_id;
            }
        }

        if ($request->status == 'Rejected') {
            if (!$isSchedule) {
                if ($request->user->role_id == $role->id) {
                    $data['title'] = $request->employee->name . ' Request Rejected';
                    $data['description'] = $request->employee->name . ' has rejected your request!';
                } else {
                    $data['title'] = $request->user->name . ' Request Rejected';
                    $data['description'] = $request->user->name . ' has rejected your request!';
                }

                $sender = $request->user_id;
                $receiver = $request->customer_id;
            } else {
                $data['title'] = $request->customer->name . ' Request Rejected';
                $data['description'] = $request->customer->name . ' has rejected your request!';
                $sender = $request->customer_id;
                $receiver = $request->user_id;
            }
        }

        GlobalHelper::makeNotification($request->id, $receiver, $data['title'], $data['description'], $sender);
        return $request;
    }

    public function rescheduleRequest($data)
    {
        $request = $this->request->where('id', $data['id'])->first();
        if (!$request) {
            return false;
        }

        $request->update([
            'schedule_id' => $data['schedule_id'],
            'status' => 'Reschedule',
            'reason' => $data['reason']
        ]);

        $request = $this->request->with(['user.images', 'user.role', 'customer', 'employee', 'service', 'schedule'])->where('id', $data['id'])->first();

        $role = $this->role->where('name', 'corporate')->first();

        if ($request->user->role->id == $role->id) {
            $title = $request->employee->name . " Reschedule Request";
            $description = $request->employee->name . " requested to reschedule request.";
        } else {
            $title = $request->user->name . " Reschedule Request";
            $description = $request->user->name . " requested to reschedule request.";
        }

        $sender = $request->user->id;
        $receiver = $request->customer->id;

        GlobalHelper::makeNotification($request->id, $receiver, $title, $description, $sender);

        return $request;
    }

    public function getRequest($id)
    {
        $request = $this->request->with(['user.images', 'user.role', 'user.address', 'customer.images', 'employee.images', 'service', 'schedule', 'feedback.replies'])->where('id', $id)->first();
        if (!$request) {
            return false;
        }

        return $request;
    }
}
