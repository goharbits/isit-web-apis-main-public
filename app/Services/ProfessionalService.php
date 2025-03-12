<?php

namespace App\Services;

use App\Facades\GlobalHelper;
use App\Interfaces\ProfessionalInterface;
use App\Models\Role;
use App\Models\User;

class ProfessionalService implements ProfessionalInterface
{
    public function __construct(
        private User $professional,
        private Role $role
    ) {}

    public function getAllProfessionals($search)
    {
        $role = $this->role->where('name', 'professional')->first();
        $query =  $this->professional->with(['role', 'images', 'address', 'services'])
            ->where('role_id', $role->id);

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhere('ss_number', 'like', "%$search%");
                });
            }

        return $query->orderBy('created_at', 'desc')->paginate(10);

    }
    public function getProfessional($id)
    {
        $role = $this->role->where('name', 'professional')->first();
        $professional = $this->professional->with(['role', 'images', 'address', 'services'])->where(['role_id' => $role->id, 'id' => $id])->first();
        if (!$professional) {
            return false;
        }
        return $professional;
    }
    public function updateProfessional($data, $id)
    {
        $professional = $this->professional->where('id', $id)->first();
        if (!$professional) {
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

        return $this->professional->with(['images', 'role', 'address','subscription'])->where('id', $id)->first();
    }
    public function deleteProfessional($id)
    {
        $role = $this->role->where('name', 'professional')->first();
        $professional = $this->professional->where(['role_id' => $role->id, 'id' => $id])->first();
        if (!$professional) {
            return false;
        }
        $professional->delete();
        return true;
    }
    public function updateStatusProfessional($status, $id)
    {
        $role = $this->role->where('name', 'professional')->first();
        $professional = $this->professional->where(['role_id' => $role->id, 'id' => $id])->first();
        if (!$professional) {
            return false;
        }
        $statusArray = ['Active', 'Block'];
        if (!in_array(ucfirst($status), $statusArray)) {
            return false;
        }
        $professional->update(['status' => ucfirst($status)]);

        if (ucfirst($status) === 'Active') {
            GlobalHelper::startFreeTrial($id);
        }

        return $professional;
    }
}
