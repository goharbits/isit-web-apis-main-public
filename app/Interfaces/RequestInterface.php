<?php

namespace App\Interfaces;

interface RequestInterface
{
    public function makeRequest($data);
    public function updateStatusRequest($data);
    public function rescheduleRequest($data);
    public function getRequest($id);
}
