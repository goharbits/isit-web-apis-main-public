<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleRequest;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    public function __construct(
        private RoleService $roleService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $roles = $this->roleService->getAll();
            return $this->success($roles);
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $role = $this->roleService->getSingleRole($id);
            if (!$role) {
                return $this->error("Role not found.", [], Response::HTTP_NOT_FOUND);
            }
            return $this->success($role);
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }

    public function store(RoleRequest $request): JsonResponse
    {
        try {
            $role = $this->roleService->createRole($request->all());
            return $this->success($role);
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }


    public function update($id, RoleRequest $request): JsonResponse
    {
        try {
            $role = $this->roleService->updateRole($id, $request->all());
            if (!$role) {
                return $this->error('Role not found.', [], Response::HTTP_NOT_FOUND);
            }
            return $this->success($role);
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $role = $this->roleService->destroyRole($id);
            if (!$role) {
                return $this->error('Role not found.', [], Response::HTTP_NOT_FOUND);
            }
            return $this->success($role, 'Role deleted.');
        } catch (\Exception $error) {
            return $this->error('error', $error->getMessage());
        }
    }
}
