<?php

namespace App\Interfaces;


interface EmployeeInterface
{
    public function getAllEmployees($id);
    public function getEmployee($id, $employee_id);
    public function createEmployee($data, $id);
    public function updateEmployee($data, $corporateId, $id);
    public function updateStatus($status, $corporateId, $id);
}
