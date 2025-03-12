<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}


    public function getStatisticsOfSuperAdmin()
    {
        try {
            $statistics = $this->dashboardService->getStatisticsOfSuperAdmin();
            return $this->success($statistics, 'Total Statistics');
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }

    public function getStatisticsOfCorporate($corporateId)
    {
        try {
            $statistics = $this->dashboardService->getStatisticsOfCorporate($corporateId);
            return $this->success($statistics, 'Total Statistics');
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }
}
