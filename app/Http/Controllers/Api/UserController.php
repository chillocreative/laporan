<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Mail\UserApprovedMail;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected ActivityLogService $activityLogService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = $this->userRepository->getWithRoles($request->integer('per_page', 15));

        // Exclude admin/super-admin users if requested (for filter dropdowns)
        if ($request->boolean('exclude_admin_roles')) {
            $users = $users->filter(function ($user) {
                return ! $user->hasAnyRole(['super-admin', 'admin']);
            });
        }

        return response()->json(UserResource::collection($users)->response()->getData(true));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $roleIds = $data['role_ids'];
        unset($data['role_ids']);

        $user = $this->userRepository->create($data);
        $this->userRepository->syncRoles($user->id, $roleIds);

        $this->activityLogService->log('user_created', $user, "User created: {$user->email}");

        return response()->json([
            'message' => 'Pengguna berjaya dicipta.',
            'data' => new UserResource($user->load('roles')),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json([
            'data' => new UserResource($user->load('roles.permissions')),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        $roleIds = $data['role_ids'] ?? null;
        unset($data['role_ids']);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user = $this->userRepository->update($user->id, $data);

        if ($roleIds !== null) {
            $this->userRepository->syncRoles($user->id, $roleIds);
        }

        $this->activityLogService->log('user_updated', $user, "User updated: {$user->email}");

        return response()->json([
            'message' => 'Pengguna berjaya dikemas kini.',
            'data' => new UserResource($user->load('roles')),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $this->activityLogService->log('user_deleted', $user, "User deleted: {$user->email}");

        $user->delete();

        return response()->json(['message' => 'Pengguna berjaya dipadam.']);
    }

    public function approve(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);

        $user->update(['is_active' => true]);
        $this->userRepository->syncRoles($user->id, [$request->input('role_id')]);

        $role = \App\Models\Role::find($request->input('role_id'));
        $this->activityLogService->log('user_approved', $user, "User approved: {$user->email} as {$role->name}");

        try {
            Mail::to($user->email)->send(new UserApprovedMail($user, $role->name));
        } catch (\Throwable $e) {
            // Log but don't fail the approval
            \Illuminate\Support\Facades\Log::warning("Failed to send approval email to {$user->email}: {$e->getMessage()}");
        }

        return response()->json([
            'message' => "Pengguna diluluskan dan diberikan peranan '{$role->name}'. Notifikasi e-mel telah dihantar.",
            'data' => new UserResource($user->load('roles')),
        ]);
    }

    public function pendingCount(): JsonResponse
    {
        $count = User::where('is_active', false)
            ->whereDoesntHave('roles')
            ->count();

        return response()->json(['data' => ['count' => $count]]);
    }

    public function toggleActive(User $user): JsonResponse
    {
        $this->authorize('deactivate', $user);

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinyahaktifkan';
        $this->activityLogService->log('user_toggle_active', $user, "User {$status}: {$user->email}");

        return response()->json([
            'message' => "Pengguna berjaya {$status}.",
            'data' => new UserResource($user->load('roles')),
        ]);
    }
}
