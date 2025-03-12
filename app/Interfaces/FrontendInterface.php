<?php

namespace App\Interfaces;

interface FrontendInterface
{
    public function filterData($data);
    public function userDetail($id);
}
