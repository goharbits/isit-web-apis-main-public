<?php

namespace App\Interfaces;

interface DashboardInterface
{
    public function getStatisticsOfSuperAdmin();
    public function getStatisticsOfCorporate($corporateId);
}
