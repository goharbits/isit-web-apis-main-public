<?php

namespace App\Interfaces;

interface ProfessionalInterface
{
    public function getAllProfessionals($search);
    public function getProfessional($id);
    public function updateProfessional($data, $id);
    public function deleteProfessional($id);
    public function updateStatusProfessional($status, $id);
}
