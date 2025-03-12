<?php

namespace App\Interfaces;

interface ProfileInterface
{
    public function updateProfessionalProfile($data, $id);
    public function updateCorporateProfile($data, $id);
    public function updateEmployeeProfile($data, $id);
    public function updateUserProfile($data, $id);
}
