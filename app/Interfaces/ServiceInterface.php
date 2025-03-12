<?php

namespace App\Interfaces;

interface ServiceInterface
{
    public function getAllServices();
    public function getService($id);
    public function storeService($data);
    public function updateService($id, $data);
    public function deleteService($id);
}
