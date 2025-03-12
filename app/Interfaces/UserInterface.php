<?php

namespace App\Interfaces;

interface UserInterface
{
    public function getAllUsers($search);
    public function getUser($id);
    public function updateUser($data, $id);
    public function deleteUser($id);
    public function updateStatusUser($status, $id);
}
