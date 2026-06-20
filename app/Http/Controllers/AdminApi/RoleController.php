<?php

namespace App\Http\Controllers\AdminApi;

use App\Data\AdminApi\Request\Role\IndexRequestData;
use App\Data\AdminApi\Request\Role\StoreRequestData;
use App\Data\AdminApi\Request\Role\UpdateRequestData;
use App\Data\AdminApi\Response\Role\RolePaginatedResponse;
use App\Data\AdminApi\Response\Role\RoleShowResponse;
use App\Enums\Auth\Guard;
use App\Enums\Permission\Name;
use App\Exceptions\ForbiddenException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminApi\Role\IndexRequest;
use App\Http\Requests\AdminApi\Role\StoreRequest;
use App\Http\Requests\AdminApi\Role\UpdateRequest;
use App\Models\Admin;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService,
    ) {
    }

    /** 角色管理-列表 */
    public function index(IndexRequest $request): JsonResponse
    {
        /** @var Admin $admin */
        $admin = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $admin->getAllPermissions()->contains('name', Name::VIEW_ROLES->value),
            exception: ForbiddenException::class
        );

        $paginator = $this->roleService->listRoles(
            data: IndexRequestData::fromRequest($request),
            guardName: Guard::ADMIN->value,
        );

        return $this->success(
            RolePaginatedResponse::fromPaginator($paginator)
        );
    }

    /** 角色管理-詳情 */
    public function show(int $id): JsonResponse
    {
        /** @var Admin $admin */
        $admin = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $admin->getAllPermissions()->contains('name', Name::VIEW_ROLES->value),
            exception: ForbiddenException::class
        );

        return $this->success(
            RoleShowResponse::fromModel($this->roleService->findOrFail($id, Guard::ADMIN->value))
        );
    }

    /** 角色管理-新增 */
    public function store(StoreRequest $request): JsonResponse
    {
        /** @var Admin $admin */
        $admin = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $admin->getAllPermissions()->contains('name', Name::ASSIGN_ROLES->value),
            exception: ForbiddenException::class
        );

        $this->roleService->createRole(
            data: StoreRequestData::fromRequest($request),
            guardName: Guard::ADMIN->value,
        );

        return $this->success([]);
    }

    /** 角色管理-編輯 */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        /** @var Admin $admin */
        $admin = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $admin->getAllPermissions()->contains('name', Name::ASSIGN_ROLES->value),
            exception: ForbiddenException::class
        );

        $this->roleService->updateRole(
            role: $this->roleService->findOrFail($id, Guard::ADMIN->value),
            data: UpdateRequestData::fromRequest($request),
        );

        return $this->success([]);
    }

    /** 角色管理-刪除 */
    public function destroy(int $id): JsonResponse
    {
        /** @var Admin $admin */
        $admin = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $admin->getAllPermissions()->contains('name', Name::ASSIGN_ROLES->value),
            exception: ForbiddenException::class
        );

        $this->roleService->deleteRole(
            $this->roleService->findOrFail($id, Guard::ADMIN->value)
        );

        return $this->success([]);
    }
}
