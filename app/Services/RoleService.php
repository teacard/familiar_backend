<?php

namespace App\Services;

use App\Data\AdminApi\Request\Role\IndexRequestData;
use App\Data\AdminApi\Request\Role\StoreRequestData;
use App\Data\AdminApi\Request\Role\UpdateRequestData;
use App\Enums\ApiCode;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnprocessableException;
use App\Models\Role;
use App\Repositories\Applications\Role\RoleRepository;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Traits\AsRepositoryProxy;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    use AsRepositoryProxy;

    public function __construct(
        protected RoleRepository $repository,
    ) {
    }

    /** 取得角色列表（含是否有 admin 使用該角色，供前端判斷可否刪除） */
    public function listRoles(IndexRequestData $data, string $guardName): Collection
    {
        return $this->get([
            'guard_name' => $guardName,
            'keyword' => $data->keyword,
        ])->load('admins');
    }

    /** 依 id 查詢角色，找不到拋 404 */
    public function findOrFail(int $id, string $guardName): Role
    {
        /** @var Role|null $role */
        $role = $this->first([
            'id' => $id,
            'guard_name' => $guardName,
        ]);

        throw_unless(
            condition: $role,
            exception: NotFoundException::class
        );

        $role->load('permissions');

        return $role;
    }

    /** 建立角色並同步權限 */
    public function createRole(StoreRequestData $data, string $guardName): void
    {
        /** @var Role $role */
        $role = $this->create(
            [
                'name' => $data->name,
                'guard_name' => $guardName,
            ]
        );

        $role->syncPermissions($data->permissions);
    }

    /** 更新角色名稱並同步權限 */
    public function updateRole(Role $role, UpdateRequestData $data): void
    {
        $role->update(['name' => $data->name]);

        $role->syncPermissions($data->permissions);
    }

    /** 刪除角色（仍有 admin 帳號使用時拋 422，不可刪除） */
    public function deleteRole(Role $role): void
    {
        throw_if(
            condition: $role->admins()->exists(),
            exception: new UnprocessableException(
                trans('api-codes.' . ApiCode::ROLE_IN_USE->value),
                ApiCode::ROLE_IN_USE,
            ),
        );

        $role->delete();
    }

    protected function getProxyRepository(): RepositoryInterface
    {
        return $this->repository;
    }
}
