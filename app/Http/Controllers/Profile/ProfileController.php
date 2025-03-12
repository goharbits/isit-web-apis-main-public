<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\EmployeeRequest;
use App\Http\Requests\Profile\CorporateRequest;
use App\Http\Requests\Profile\ProfessionalRequest;
use App\Http\Requests\Profile\UserRequest;
use App\Services\ProfileService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService
    ) {}
    public function updateProfessionalProfile(ProfessionalRequest $request, $id)
    {
        try {
            $professional = $this->profileService->updateProfessionalProfile($request->all(), $id);
            if (!$professional) {
                return $this->error('Unauthorized user to update profile.', [], 403);
            }
            return $this->success($professional, 'Profile updated successfully!');
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }
    public function updateCorporateProfile(CorporateRequest $request, $id)
    {
        try {

            $corporate = $this->profileService->updateCorporateProfile($request->all(), $id);
            if (!$corporate) {
                return $this->error('Unauthorized user to update profile.', [], 403);
            }
            return $this->success($corporate, 'Profile updated successfully!');
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }
    public function updateEmployeeProfile(EmployeeRequest $request, $id)
    {
        try {
            $employee = $this->profileService->updateEmployeeProfile($request->all(), $id);
            if (!$employee) {
                return $this->error('Unauthorized user to update profile.', [], 403);
            }
            return $this->success($employee, 'Profile updated successfully!');
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }
    public function updateUserProfile(UserRequest $request, $id)
    {
        try {
            $user = $this->profileService->updateUserProfile($request->all(), $id);
            if (!$user) {
                return $this->error('Unauthorized user to update profile.', [], 403);
            }
            return $this->success($user, 'Profile updated successfully!');
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }
}
