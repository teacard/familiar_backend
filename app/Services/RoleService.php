<?php

namespace App\Services;

use App\Data\AdminApi\Request\Role\IndexRequestData;
use App\Data\AdminApi\Request\Role\StoreRequestData;
use App\Data\AdminApi\Request\Role\UpdateRequestData;
use App\Enums\Auth\Guard;
use App\Exceptions\NotFoundException;
use App\Models\Role;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Applications\Role\RoleRepository;
use App\Repositories\Traits\AsRepositoryProxy;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    use AsRepositoryProxy;

    public function __construct(
        protected RoleRepository $repository,
    ) {}

    protected function getProxyRepository(): RepositoryInterface
    {
        return $this->repository;
    }

    /** 取得所有 admin guard 角色（含權限） */
    public function listRoles(IndexRequestData $data): Collection
    {
        return $this->get([
            'guard_name'       => Guard::ADMIN->value,
            'with_permissions' => true,
            'keyword'          => $data->keyword,
        ]);
    }

    /** 依 id 查詢 admin guard 角色（含權限），找不到拋 404 */
    public function findOrFail(int $id): Role
    {
        /** @var Role|null $role */
        $role = $this->first([
            'id' => $id,
            'guard_name' => Guard::ADMIN->value,
        ]);

        throw_unless(
            condition: $role,
            exception: NotFoundException::class
        );

        $role->load('permissions');

        return $role;
    }

    /** 建立 admin guard 角色並同步權限 */
    public function createRole(StoreRequestData $data): void
    {
        /** @var Role $role */
        $role = $this->create(['name' => $data->name, 'guard_name' => Guard::ADMIN->value]);
        $role->syncPermissions($data->permissions);
    }

    /** 更新角色名稱並同步權限 */
    public function updateRole(Role $role, UpdateRequestData $data): void
    {
        $role->update(['name' => $data->name]);
        $role->syncPermissions($data->permissions);
    }

    /** 刪除角色 */
    public function deleteRole(Role $role): void
    {
        $role->delete();
    }
}
