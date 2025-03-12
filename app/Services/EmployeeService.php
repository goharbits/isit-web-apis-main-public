<?php

namespace App\Services;

use App\Facades\GlobalHelper;
use App\Interfaces\EmployeeInterface;
use App\Mail\EmployeeMail;
use App\Models\Address;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;


class EmployeeService implements EmployeeInterface
{
    public function __construct(
        private User $employee,
        private Role $role,
        private Address $address
    ) {}

    public function getAllEmployees($id)
    {
        return $this->employee->with(['address', 'role', 'corporate', 'images', 'services'])
            ->where('parent_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }
    public function getEmployee($id, $employee_id)
    {
        return $this->employee->with(['address', 'role', 'corporate', 'images', 'services'])->where([
            'parent_id' => $id,
            'id' => $employee_id
        ])->first();
    }
    public function createEmployee($data, $id)
    {
        if (!GlobalHelper::hasRole($id, 'corporate')) {
            return false;
        }

        $role = $this->role->where('name', 'employee')->first();

        $address = GlobalHelper::addAddress($data);
        $data['address_id'] = $address;
        $data['role_id'] = $role->id;
        $data['parent_id'] = $id;
        $data['password'] = Hash::make($data['password']);
        $data['status'] = 'Active';
        $data['email_verified_at'] = now();
        $employee = $this->employee->create($data);

        if (!$employee) {
            return false;
        }

        $image = [];

        array_push($image, [
            'path' => GlobalHelper::uploadFile($data['profile'], "uploads/profile"),
            'type' => 'profile'
        ]);

        array_push($image, [
            'path' => GlobalHelper::uploadFile($data['other'], "uploads/other"),
            'type' => 'other'
        ]);

        $employee->images()->createMany($image);

        Mail::to($employee->email)->send(new EmployeeMail($employee));

        return $employee;
    }
    public function updateStatus($status, $corporateId, $id)
    {
        $role = $this->role->where('name', 'corporate')->first();
        $corporate = $this->employee->where(['id' => $corporateId, 'role_id' => $role->id])->first();
        if (!$corporate) {
            return false;
        }

        $employee = $this->employee->where('id', $id)->first();
        if (!$employee) {
            return false;
        }

        $statusArray = ['Active', 'Block'];
        if (!in_array(ucfirst($status), $statusArray)) {
            return false;
        }

        $employee->update([
            'status' => ucfirst($status)
        ]);
        return $employee;
    }
    public function updateEmployee($data, $corporateId, $id)
    {
        $role = $this->role->where('name', 'corporate')->first();
        $corporate = $this->employee->where(['id' => $corporateId, 'role_id' => $role->id])->first();
        if (!$corporate) {
            return false;
        }

        $employee = $this->employee->where('id', $id)->first();
        if (!$employee) {
            return false;
        }
        $image = [];
        if (isset($data['profile'])) {
            array_push($image, [
                'path' => GlobalHelper::uploadFile($data['profile'], "uploads/profile"),
                'type' => 'profile'
            ]);
        }
        if (isset($data['certificate'])) {
            array_push($image, [
                'path' => GlobalHelper::uploadFile($data['certificate'], "uploads/certificate"),
                'type' => 'certificate'
            ]);
        }
        if (isset($data['other'])) {
            array_push($image, [
                'path' => GlobalHelper::uploadFile($data['other'], "uploads/other"),
                'type' => 'other'
            ]);
        }

        if ($data['address'] && $data['longitude'] && $data['latitude'] && $employee->address_id) {
            $this->address->where('id', $employee->address_id)->update([
                'address' => $data['address'],
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude']
            ]);
        } else {
            $address = $this->address->create([
                'address' => $data['address'],
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude']
            ]);
            $data['address_id'] = $address->id;
        }

        unset($data['email']);
        $employee->update($data);
        foreach ($image as $img) {
            $employee->images()->updateOrCreate([
                'type' => $img['type'],
                'imageable_id' => $employee->id
            ], [
                'path' => $img['path'],
                'type' => $img['type']
            ]);
        }
        return $this->employee->with(['corporate', 'images', 'role', 'address','subscription'])->where('id', $id)->first();
    }
}
