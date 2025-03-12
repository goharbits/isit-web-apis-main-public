<?php

namespace App\Services;

use App\Facades\GlobalHelper;
use App\Interfaces\UserInterface;
use App\Models\Role;
use App\Models\User;

class UserService implements UserInterface
{
    public function __construct(
        private User $user,
        private Role $role
    ) {}

    public function getAllUsers($search)
    {

        $role = $this->role->where('name', 'user')->first();

        $query =  $this->user->with(['role', 'images', 'address'])
             ->where('role_id', $role->id);

            if ($search) {

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhere('ss_number', 'like', "%$search%");
                });
            }

        return   $query->orderBy('created_at', 'desc')->paginate(10);



    }
    public function getUser($id)
    {
        $role = $this->role->where('name', 'user')->first();
        $user = $this->user->with(['role', 'images', 'address'])->where(['role_id' => $role->id, 'id' => $id])->first();
        if (!$user) {
            return false;
        }
        return $user;
    }
    public function updateUser($data, $id)
    {
        $user = $this->user->where('id', $id)->first();
        if (!$user) {
            return false;
        }

        $image = [];
        if (isset($data['profile'])) {
            array_push($image, [
                'path' => GlobalHelper::uploadFile($data['profile'], "uploads/profile"),
                'type' => 'logo'
            ]);
        }
        if (isset($data['certificate'])) {
            array_push($image, [
                'path' => GlobalHelper::uploadFile($data['certificate'], "uploads/certificate"),
                'type' => 'other'
            ]);
        }
        if (isset($data['other'])) {
            array_push($image, [
                'path' => GlobalHelper::uploadFile($data['other'], "uploads/other"),
                'type' => 'other'
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

        return $this->user->with(['images', 'role', 'address','subscription'])->where('id', $id)->first();
    }
    public function deleteUser($id)
    {
        $role = $this->role->where('name', 'user')->first();
        $user = $this->user->where(['role_id' => $role->id, 'id' => $id])->first();
        if (!$user) {
            return false;
        }
        $user->delete();
        return true;
    }
    public function updateStatusUser($status, $id)
    {
        $role = $this->role->where('name', 'user')->first();
        $user = $this->user->where(['role_id' => $role->id, 'id' => $id])->first();
        if (!$user) {
            return false;
        }
        $statusArray = ['Active', 'Block'];
        if (!in_array(ucfirst($status), $statusArray)) {
            return false;
        }
        $user->update(['status' => ucfirst($status)]);
        return $user;
    }
}
