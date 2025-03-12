<?php

namespace App\Http\Controllers\AvailService;

use App\Http\Controllers\Controller;
use App\Services\AvailService;
use Illuminate\Http\Request;

class AvailServiceController extends Controller
{
    public function __construct(
        private AvailService $availService
    ) {}

    public function getAvailServices($id)
    {
        try {
            $availServices = $this->availService->getAvailServices($id);
            return $this->success($availServices, 'Availed Services');
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }
}
