<?php

namespace App\Http\Controllers\AdminApi;

use App\Data\AdminApi\Request\Player\IndexRequestData;
use App\Data\AdminApi\Request\Player\StoreRequestData;
use App\Data\AdminApi\Request\Player\UpdateRequestData;
use App\Data\AdminApi\Response\Player\PlayerPaginatedResponse;
use App\Data\AdminApi\Response\Player\PlayerShowResponse;
use App\Enums\Auth\Guard;
use App\Enums\Permission\Name;
use App\Exceptions\ForbiddenException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminApi\Player\IndexRequest;
use App\Http\Requests\AdminApi\Player\StoreRequest;
use App\Http\Requests\AdminApi\Player\UpdateRequest;
use App\Models\Admin;
use App\Services\PlayerService;
use Illuminate\Http\JsonResponse;

class PlayerController extends Controller
{
    public function __construct(
        protected PlayerService $playerService,
    ) {
    }

    /** 遊戲會員管理-列表 */
    public function index(IndexRequest $request): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::VIEW_PLAYERS->value),
            exception: ForbiddenException::class
        );

        $paginator = $this->playerService->listPlayers(
            IndexRequestData::fromRequest($request)
        );

        return $this->success(
            PlayerPaginatedResponse::fromPaginator($paginator)
        );
    }

    /** 遊戲會員管理-新增 */
    public function store(StoreRequest $request): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::CREATE_PLAYERS->value),
            exception: ForbiddenException::class
        );

        $this->playerService->createPlayer(
            StoreRequestData::fromRequest($request)
        );

        return $this->success([]);
    }

    /** 遊戲會員管理-詳情 */
    public function show(int $id): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::VIEW_PLAYERS->value),
            exception: ForbiddenException::class
        );

        $player = $this->playerService->findOrFail($id);

        return $this->success(PlayerShowResponse::fromModel($player));
    }

    /** 遊戲會員管理-編輯 */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::EDIT_PLAYERS->value),
            exception: ForbiddenException::class
        );

        $this->playerService->updatePlayer(
            player: $this->playerService->findOrFail($id),
            data: UpdateRequestData::fromRequest($request),
        );

        return $this->success([]);
    }

    /** 遊戲會員管理-刪除 */
    public function destroy(int $id): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::DELETE_PLAYERS->value),
            exception: ForbiddenException::class
        );

        $this->playerService->deletePlayer(
            $this->playerService->findOrFail($id)
        );

        return $this->success([]);
    }
}
