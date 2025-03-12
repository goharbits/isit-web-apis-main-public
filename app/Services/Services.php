<?php

namespace App\Services;

use App\Interfaces\ServiceInterface;
use App\Models\Service;

class Services implements ServiceInterface
{
    public function __construct(
        private Service $service
    ) {}

    public function getAllServices()
    {
        return $this->service->get();
    }
    public function getService($id)
    {
        return $this->service->where('id', $id)->first();
    }
    public function storeService($data)
    {
        $service = $this->service->create($data);
        if (!$service) {
            return false;
        }
        return $service;
    }
    public function updateService($id, $data)
    {
        $service = $this->service->where(['id' => $id])->first();
        if (!$service) {
            return false;
        }
        $service->update($data);
        return $service;
    }
    public function deleteService($id)
    {
        $service = $this->service->where('id', $id)->first();

        if (!$service) {
            return false;
        }
        $service->delete();
        return true;
    }

    public function getUserServices($userId)
    {
        $service = $this->service->withCount('feedback')
            ->withAvg('feedback', 'stars')
            ->where('user_id', $userId)->get();

        return $service;
    }
}
