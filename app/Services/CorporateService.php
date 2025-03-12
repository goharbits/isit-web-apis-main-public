<?php

namespace App\Services;

use App\Facades\GlobalHelper;
use App\Interfaces\CorporateInterface;
use App\Models\Role;
use App\Models\User;


class CorporateService implements CorporateInterface
{
    public function __construct(
        private User $corporate,
        private Role $role
    ) {}

    public function getAllCorporates($search)
    {
        $role = $this->role->where('name', 'corporate')->first();
        $query =  $this->corporate->with(['role', 'images', 'address'])
            ->where('role_id', $role->id);

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhere('phone_no', 'like', "%$search%");
                });
            }

        return   $query->orderBy('created_at', 'desc')->paginate(10);

    }
    public function getCorporate($id)
    {
        $role = $this->role->where('name', 'corporate')->first();
        $corporate = $this->corporate->with(['role', 'images', 'address'])->where(['role_id' => $role->id, 'id' => $id])->first();
        if (!$corporate) {
            return false;
        }
        return $corporate;
    }
    public function updateCorporate($data, $id)
    {
        $corporate = $this->corporate->where('id', $id)->first();
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
        return $this->corporate->with(['images', 'role', 'address','subscription'])->where('id', $id)->first();
    }
    public function deleteCorporate($id)
    {
        $role = $this->role->where('name', 'corporate')->first();
        $corporate = $this->corporate->where(['role_id' => $role->id, 'id' => $id])->first();
        if (!$corporate) {
            return false;
        }
        $corporate->delete();
        return true;
    }
    public function updateStatusCorporate($status, $id)
    {
        $role = $this->role->where('name', 'corporate')->first();
        $corporate = $this->corporate->where(['role_id' => $role->id, 'id' => $id])->first();
        if (!$corporate) {
            return false;
        }
        $statusArray = ['Active', 'Block'];
        if (!in_array(ucfirst($status), $statusArray)) {
            return false;
        }
        $corporate->update(['status' => ucfirst($status)]);

        if (ucfirst($status) === 'Active') {
            GlobalHelper::startFreeTrial($id);
        }

        return $corporate;
    }
}
