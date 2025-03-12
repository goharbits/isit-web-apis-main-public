<?php

namespace App\Services;

use App\Interfaces\FrontendInterface;
use App\Models\Request;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FrontendService implements FrontendInterface
{

    public function __construct(
        private Service $service,
        private User $user,
        private Request $request,
        private Role $role
    ) {}
    public function filterData($data)
    {
        $latitude = $data['latitude'];
        $longitude = $data['longitude'];
        $radius = $data['radius'];

        $services = $this->service
            ->whereHas('user', function ($query) use ($latitude, $longitude, $radius) {
                $query->where('status', 'Active');
                if ($radius !== 'all') {
                    $query->whereHas('address', function ($query) use ($latitude, $longitude, $radius) {
                        $query->addSelect(DB::raw("
                        (6371 * acos(
                            cos(radians($latitude)) *
                            cos(radians(latitude)) *
                            cos(radians(longitude) - radians($longitude)) +
                            sin(radians($latitude)) *
                            sin(radians(latitude))
                        )) AS distance
                    "))
                            ->having('distance', '<=', $radius); // Filter users within the radius
                    });
                }
            });


        if (isset($data['name'])) {
            $services = $services->whereIn('name', $data['name']);
        }
        if (isset($data['start_price']) && isset($data['end_price'])) {
            $services = $services->whereBetween('price', [$data['start_price'], $data['end_price']]);
        }

        $services = $services->with(['user.address', 'user.images', 'user.services'])
            ->get();

        return $services->pluck('user')->unique();
    }

    public function userDetail($id)
    {
        $user = $this->user->with([
            'images',
            'schedules',
            'role',
            'services.feedback'
        ])->where('id', $id)->first();
        return $user;
    }


    public function getPopularProfessional($data)
    {
        // $latitude = 31.462199;
        // $longitude = 74.294221;

        $latitude = $data['latitude'];
        $longitude = $data['longitude'];
        $radius = 25;

        $role = $this->role->where('name', 'professional')->first();

        $popularProfessional = User::where('role_id', $role->id)
            ->whereHas('requests', function ($query) {
                $query->where('status', 'Completed');
            })
            ->withCount(['requests as total_services_done' => function ($query) {
                $query->where('status', 'Completed');
            }])
            ->with([
                'role',
                'requests.feedback',
                'images',
                'address',
                'requests' => function ($query) {
                    $query->where('status', 'Completed');
                }
            ])
            ->whereHas('address', function ($query) use ($latitude, $longitude, $radius) {
                $query->addSelect(DB::raw("
                (6371 * acos(
                    cos(radians($latitude)) *
                    cos(radians(latitude)) *
                    cos(radians(longitude) - radians($longitude)) +
                    sin(radians($latitude)) *
                    sin(radians(latitude))
                )) AS distance
            "))->having('distance', '<=', $radius);
            })
            ->where('status', 'Active')
            ->orderByDesc('total_services_done')
            ->get();

        return $popularProfessional;
    }
}
