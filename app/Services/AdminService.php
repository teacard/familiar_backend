<?php

namespace App\Services;

use App\Data\AdminApi\Request\Admin\IndexRequestData;
use App\Data\AdminApi\Request\Admin\StoreRequestData;
use App\Data\AdminApi\Request\Admin\UpdateRequestData;
use App\Data\AdminApi\Request\Admin\UpdateStatusRequestData;
use App\Enums\Auth\Guard;
use App\Exceptions\NotFoundException;
use App\Models\Admin;
use App\Repositories\Applications\Admin\AdminRepository;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Traits\AsRepositoryProxy;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminService
{
    use AsRepositoryProxy;

    public function __construct(
        protected AdminRepository $repository,
        protected RoleService $roleService,
    ) {
    }

    /** 列表（含角色 eager load） */
    public function listAdmins(IndexRequestData $data): LengthAwarePaginator
    {
        $paginator = $this->paginate(
            perPage: $data->perPage,
            page: $data->page,
            filters: [
                'keyword' => $data->keyword,
                'status' => $data->status?->value,
                'hasRole' => is_null($data->roleId) ? null : [
                    'id' => $data->roleId,
                ],
            ],
        );

        $paginator->getCollection()->load('roles');

        return $paginator;
    }

    /** 單筆查詢（含角色 eager load，找不到拋 404） */
    public function findOrFail(int $id): Admin
    {
        $admin = $this->findById($id);

        throw_unless(
            condition: $admin,
            exception: NotFoundException::class,
        );

        $admin->load('roles');

        return $admin;
    }

    /** 建立後台人員並指派角色 */
    public function createAdmin(StoreRequestData $data): Admin
    {
        $admin = $this->create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
            'status' => $data->status->value,
        ]);

        $admin->assignRole($this->roleService->findOrFail($data->roleId, Guard::ADMIN->value));

        return $admin;
    }

    /** 更新後台人員資料並同步角色 */
    public function updateAdmin(Admin $admin, UpdateRequestData $data): void
    {
        $fields = ['name' => $data->name, 'email' => $data->email, 'status' => $data->status->value];

        if (null !== $data->password) {
            $fields['password'] = $data->password;
        }

        $admin->update($fields);
        $admin->syncRoles([$this->roleService->findOrFail($data->roleId, Guard::ADMIN->value)]);
    }

    /** 更新帳號狀態 */
    public function updateAdminStatus(Admin $admin, UpdateStatusRequestData $data): void
    {
        $admin->update(['status' => $data->status->value]);
    }

    /** 軟刪除後台人員 */
    public function deleteAdmin(Admin $admin): void
    {
        $admin->delete();
    }

    protected function getProxyRepository(): RepositoryInterface
    {
        return $this->repository;
    }
}
