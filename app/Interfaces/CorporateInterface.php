<?php

namespace App\Interfaces;

interface CorporateInterface
{
    public function getAllCorporates($search);
    public function getCorporate($id);
    public function updateCorporate($data, $id);
    public function deleteCorporate($id);
    public function updateStatusCorporate($status, $id);
}
