<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\SettingRequest;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(
        private SettingService $settingService
    ) {}


    public function resetPassword(SettingRequest $request)
    {
        try {
            $setting = $this->settingService->resetPassword($request->all());
            if (!$setting) {
                return $this->error('Sorry, something went wrong!');
            }

            return $this->success($setting, 'Password has been reset.');
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }
}
