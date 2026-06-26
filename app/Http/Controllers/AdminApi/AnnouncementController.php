<?php

namespace App\Http\Controllers\AdminApi;

use App\Data\AdminApi\Request\Announcement\IndexRequestData;
use App\Data\AdminApi\Request\Announcement\StoreRequestData;
use App\Data\AdminApi\Request\Announcement\UpdateRequestData;
use App\Data\AdminApi\Response\Announcement\AnnouncementPaginatedResponse;
use App\Data\AdminApi\Response\Announcement\AnnouncementResponse;
use App\Enums\Auth\Guard;
use App\Enums\Permission\Name;
use App\Exceptions\ForbiddenException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminApi\Announcement\IndexRequest;
use App\Http\Requests\AdminApi\Announcement\StoreRequest;
use App\Http\Requests\AdminApi\Announcement\UpdateRequest;
use App\Models\Admin;
use App\Services\AnnouncementService;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    public function __construct(
        protected AnnouncementService $announcementService,
    ) {
    }

    /** 公告管理-列表 */
    public function index(IndexRequest $request): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::VIEW_ANNOUNCEMENTS->value),
            exception: ForbiddenException::class,
        );

        $paginator = $this->announcementService->listAnnouncements(
            IndexRequestData::fromRequest($request)
        );

        return $this->success(
            AnnouncementPaginatedResponse::fromPaginator($paginator)
        );
    }

    /** 公告管理-新增 */
    public function store(StoreRequest $request): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::CREATE_ANNOUNCEMENTS->value),
            exception: ForbiddenException::class,
        );

        $this->announcementService->createAnnouncement(
            StoreRequestData::fromRequest($request)
        );

        return $this->success([]);
    }

    /** 公告管理-詳情 */
    public function show(int $id): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::VIEW_ANNOUNCEMENTS->value),
            exception: ForbiddenException::class,
        );

        return $this->success(
            AnnouncementResponse::fromModel($this->announcementService->findOrFail($id))
        );
    }

    /** 公告管理-編輯 */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::EDIT_ANNOUNCEMENTS->value),
            exception: ForbiddenException::class,
        );

        $this->announcementService->updateAnnouncement(
            announcement: $this->announcementService->findOrFail($id),
            data: UpdateRequestData::fromRequest($request),
        );

        return $this->success([]);
    }

    /** 公告管理-刪除 */
    public function destroy(int $id): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::DELETE_ANNOUNCEMENTS->value),
            exception: ForbiddenException::class,
        );

        $this->announcementService->deleteAnnouncement(
            $this->announcementService->findOrFail($id)
        );

        return $this->success([]);
    }
}
