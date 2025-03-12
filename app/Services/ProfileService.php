<?php

namespace App\Services;

use App\Facades\GlobalHelper;
use App\Interfaces\ProfileInterface;
use App\Models\User;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;

class ProfileService implements ProfileInterface
{
    public function __construct(
        private User $user,
        private Address $address
    ) {}

    public function updateProfessionalProfile($data, $id)
    {
        $professional = $this->user->where('id', $id)->first();
        if (!$professional) {
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

        if ($data['address'] && $data['longitude'] && $data['latitude'] && $professional->address_id) {
            $this->address->where('id', $professional->address_id)->update([
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

        $professional->update($data);

        foreach ($image as $img) {
            $professional->images()->updateOrCreate([
                'type' => $img['type'],
                'imageable_id' => $professional->id
            ], [
                'path' => $img['path'],
                'type' => $img['type']
            ]);
        }

        return $this->user->with(['images', 'role', 'address','subscription'])->where('id', $id)->first();
    }
    public function updateCorporateProfile($data, $id)
    {
        $corporate = $this->user->where('id', $id)->first();
        if (!$corporate) {
            return false;
        }

        $image = [];
        if (isset($data['logo'])) {
            array_push($image, [
                'path' => GlobalHelper::uploadFile($data['logo'], "uploads/logo"),
                'type' => 'logo'
            ]);
        }
        if (isset($data['other'])) {
            array_push($image, [
                'path' => GlobalHelper::uploadFile($data['other'], "uploads/other"),
                'type' => 'other'
            ]);
        }

        if ($data['address'] && $data['longitude'] && $data['latitude'] && $corporate->address_id) {
            $this->address->where('id', $corporate->address_id)->update([
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

        $corporate->update($data);

        foreach ($image as $img) {
            $corporate->images()->updateOrCreate([
                'type' => $img['type'],
                'imageable_id' => $corporate->id
            ], [
                'path' => $img['path'],
                'type' => $img['type']
            ]);
        }
        return $this->user->with(['images', 'role', 'address','subscription'])->where('id', $id)->first();
    }
    public function updateEmployeeProfile($data, $id)
    {
        $employee = $this->user->where('id', $id)->first();
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
        return $this->user->with(['corporate', 'images', 'role', 'address','subscription'])->where('id', $id)->first();
    }
    public function updateUserProfile($data, $id)
    {

        $user = $this->user->where('id', $id)->first();
        if (!$user) {
            return false;
        }
        $image = [];

        if (isset($data['profile'])) {
            array_push($image, [
                'path' => GlobalHelper::uploadFile($data['profile'], "uploads/profile"),
                'type' => 'profile'
            ]);
        }
        $user->update($data);
        foreach ($image as $img) {
            $user->images()->updateOrCreate([
                'type' => $img['type'],
                'imageable_id' => $user->id
            ], [
                'path' => $img['path'],
                'type' => $img['type']
            ]);
        }
        return $this->user->with(['images', 'role', 'address'])->where('id', $id)->first();
    }
}
