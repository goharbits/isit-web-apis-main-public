<?php

namespace App\Interfaces;

interface RoleInterface
{
    public function getAll();

    public function getSingleRole($id);

    public function createRole($data);

    public function updateRole($id, $data);

    public function destroyRole($id);
}
