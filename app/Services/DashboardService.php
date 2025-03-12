<?php

namespace App\Services;

use App\Interfaces\DashboardInterface;
use App\Models\Service;
use App\Models\User;

class DashboardService implements DashboardInterface
{

    public function __construct(
        private User $user,
        private Service $service
    ) {}
    public function getStatisticsOfSuperAdmin()
    {
        $totalCorporates = $this->user->whereHas('role', function ($query) {
            $query->where('name', 'corporate');
        })->count();

        $totalProfessionals = $this->user->whereHas('role', function ($query) {
            $query->where('name', 'professional');
        })->count();

        $totalUsers = $this->user->whereHas('role', function ($query) {
            $query->where('name', 'user');
        })->count();

        $totalEmployees = $this->user->whereHas('role', function ($query) {
            $query->where('name', 'employee');
        })->count();

        return [
            'total_corporates' => $totalCorporates,
            'total_professionals' => $totalProfessionals,
            'total_employees' => $totalEmployees,
            'total_users' => $totalUsers
        ];
    }


    public function getStatisticsOfCorporate($corporateId)
    {
        $totalEmployees = $this->user->where('parent_id', $corporateId)
            ->whereHas('role', function ($query) {
                $query->where('name', 'employee');
            })->count();

        $totalServices = $this->service->where('corporate_id', $corporateId)->count();

        return [
            'total_employees' => $totalEmployees,
            'total_services' => $totalServices
        ];
    }
}
