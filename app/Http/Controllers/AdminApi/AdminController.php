<?php

namespace App\Http\Controllers\AdminApi;

use App\Data\AdminApi\Request\Admin\IndexRequestData;
use App\Data\AdminApi\Request\Admin\StoreRequestData;
use App\Data\AdminApi\Request\Admin\UpdateRequestData;
use App\Data\AdminApi\Request\Admin\UpdateStatusRequestData;
use App\Data\AdminApi\Response\Admin\AdminPaginatedResponse;
use App\Data\AdminApi\Response\Admin\AdminShowResponse;
use App\Enums\Auth\Guard;
use App\Enums\Permission\Name;
use App\Exceptions\ForbiddenException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminApi\Admin\IndexRequest;
use App\Http\Requests\AdminApi\Admin\StoreRequest;
use App\Http\Requests\AdminApi\Admin\UpdateRequest;
use App\Http\Requests\AdminApi\Admin\UpdateStatusRequest;
use App\Models\Admin;
use App\Services\AdminService;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct(
        protected AdminService $adminService,
        protected MediaService $mediaService,
    ) {
    }

    /** 後台人員管理-列表 */
    public function index(IndexRequest $request): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::VIEW_USERS->value),
            exception: ForbiddenException::class
        );

        $paginator = $this->adminService->listAdmins(
            IndexRequestData::fromRequest($request)
        );

        return $this->success(
            AdminPaginatedResponse::fromPaginator($paginator)
        );
    }

    /** 後台人員管理-新增 */
    public function store(StoreRequest $request): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::CREATE_USERS->value),
            exception: ForbiddenException::class
        );

        $data = StoreRequestData::fromRequest($request);

        DB::transaction(function () use ($data): void {
            $admin = $this->adminService->createAdmin($data);

            if (!is_null($data->mediaId)) {
                $this->mediaService->transferToAdmin($data->mediaId, $admin);
            }
        });

        return $this->success([]);
    }

    /** 後台人員管理-詳情 */
    public function show(int $id): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();
        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::VIEW_USERS->value),
            exception: ForbiddenException::class
        );

        $admin = $this->adminService->findOrFail($id);
        $admin->load('media');

        return $this->success(AdminShowResponse::fromModel($admin));
    }

    /** 後台人員管理-編輯 */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();
        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::EDIT_USERS->value),
            exception: ForbiddenException::class
        );

        $admin = $this->adminService->findOrFail($id);
        $data = UpdateRequestData::fromRequest($request);

        DB::transaction(function () use ($admin, $data): void {
            $this->adminService->updateAdmin(admin: $admin, data: $data);

            if (null !== $data->mediaId) {
                $this->mediaService->transferToAdmin($data->mediaId, $admin);
            }
        });

        return $this->success([]);
    }

    /** 後台人員管理-狀態切換 */
    public function updateStatus(UpdateStatusRequest $request, int $id): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::EDIT_USERS->value),
            exception: ForbiddenException::class
        );

        $this->adminService->updateAdminStatus(
            admin: $this->adminService->findOrFail($id),
            data: UpdateStatusRequestData::fromRequest($request),
        );

        return $this->success([]);
    }

    /** 後台人員管理-刪除 */
    public function destroy(int $id): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();
        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::DELETE_USERS->value),
            exception: ForbiddenException::class
        );

        $this->adminService->deleteAdmin(
            $this->adminService->findOrFail($id)
        );

        return $this->success([]);
    }
}
