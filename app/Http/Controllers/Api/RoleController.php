<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function __construct(
        protected RoleRepositoryInterface $roleRepository,
        protected ActivityLogService $activityLogService,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        $roles = $this->roleRepository->getWithPermissions();

        return response()->json([
            'data' => RoleResource::collection($roles),
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $permissionIds = $data['permission_ids'] ?? [];
        unset($data['permission_ids']);

        $role = $this->roleRepository->create($data);

        if (! empty($permissionIds)) {
            $this->roleRepository->syncPermissions($role->id, $permissionIds);
        }

        $this->activityLogService->log('role_created', $role, "Role created: {$role->name}");

        return response()->json([
            'message' => 'Peranan berjaya dicipta.',
            'data' => new RoleResource($role->load('permissions')),
        ], 201);
    }

    public function show(Role $role): JsonResponse
    {
        $this->authorize('view', $role);

        return response()->json([
            'data' => new RoleResource($role->load('permissions')),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $data = $request->validated();
        $permissionIds = $data['permission_ids'] ?? null;
        unset($data['permission_ids']);

        $role = $this->roleRepository->update($role->id, $data);

        if ($permissionIds !== null) {
            $this->roleRepository->syncPermissions($role->id, $permissionIds);
        }

        $this->activityLogService->log('role_updated', $role, "Role updated: {$role->name}");

        return response()->json([
            'message' => 'Peranan berjaya dikemas kini.',
            'data' => new RoleResource($role->load('permissions')),
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->authorize('delete', $role);

        $this->activityLogService->log('role_deleted', $role, "Role deleted: {$role->name}");

        $role->delete();

        return response()->json(['message' => 'Peranan berjaya dipadam.']);
    }

    public function permissions(): JsonResponse
    {
        $permissions = Permission::all()->groupBy('group_name');

        return response()->json(['data' => $permissions]);
    }

    public function assignable(): JsonResponse
    {
        // Roles available for self-registration (exclude super-admin)
        $roles = Role::where('slug', '!=', 'super-admin')
            ->select('id', 'name', 'slug')
            ->get();

        return response()->json(['data' => $roles]);
    }
}
