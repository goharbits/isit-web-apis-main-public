<?php

namespace App\Http\Controllers\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request\RescheduleRequest;
use App\Http\Requests\Request\ValidRequest;
use App\Http\Requests\Request\ValidUpdateRequest;
use App\Services\RequestService;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function __construct(
        private RequestService $requestService
    ) {}

    public function getRequest($id)
    {
        try {
            $request = $this->requestService->getRequest($id);
            if (!$request) {
                return $this->error('Sorry, request not found.', [], 404);
            }
            return $this->success($request);
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }

    public function sendRequest(ValidRequest $request)
    {
        try {
            $user = $this->requestService->makeRequest($request->all());
            if (!$user) {
                return $this->error('Sorry, unable to make request.', [], 403);
            }
            return $this->success($user, 'Request created successfully!');
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }

    public function updateRequest(ValidUpdateRequest $request)
    {
        try {
            $user = $this->requestService->updateStatusRequest($request->all());
            if (!$user) {
                return $this->error('Unathenticate user to perform this action.', [], 403);
            }
            return $this->success($user, 'Request status updated successfully!');
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }

    public function rescheduleRequest(RescheduleRequest $request)
    {
        try {
            $user = $this->requestService->rescheduleRequest($request->all());
            if (!$user) {
                return $this->error('Unathenticate user to perform this action.', [], 403);
            }
            return $this->success($user, 'Request reschedule successfully!');
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }
}
