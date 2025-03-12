<?php

namespace App\Services;

use App\Interfaces\RoleInterface;
use App\Models\Role;

class RoleService implements RoleInterface
{
    public function __construct(
        private Role $role
    ) {}
    public function getAll()
    {
        return $this->role->get();
    }
    public function getSingleRole($id)
    {
        return $this->role->where('id', $id)->first();
    }
    public function createRole($data)
    {
        return $this->role->create($data);
    }
    public function updateRole($id, $data)
    {
        return $this->role->where('id', $id)->update($data);
    }
    public function destroyRole($id)
    {
        return $this->role->where('id', $id)->delete();
    }
}
