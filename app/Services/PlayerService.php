<?php

namespace App\Services;

use App\Data\AdminApi\Request\Player\IndexRequestData;
use App\Data\AdminApi\Request\Player\StoreRequestData;
use App\Data\AdminApi\Request\Player\UpdateRequestData;
use App\Enums\ApiCode;
use App\Enums\Player\Status;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnprocessableException;
use App\Models\Player;
use App\Repositories\Applications\Player\PlayerRepository;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Traits\AsRepositoryProxy;
use Illuminate\Pagination\LengthAwarePaginator;

class PlayerService
{
    use AsRepositoryProxy;

    public function __construct(
        protected PlayerRepository $repository,
    ) {
    }

    /** 列表（關鍵字 + 狀態篩選 + 分頁） */
    public function listPlayers(IndexRequestData $data): LengthAwarePaginator
    {
        return $this->paginate(
            perPage: $data->perPage,
            page: $data->page,
            filters: [
                'keyword' => $data->keyword,
                'status' => $data->status?->value,
            ],
        );
    }

    /** 單筆查詢（找不到拋 404） */
    public function findOrFail(int $id): Player
    {
        /** @var Player|null $player */
        $player = $this->findById($id);

        throw_if(
            condition: is_null($player),
            exception: NotFoundException::class,
        );

        return $player;
    }

    /** 建立玩家：自動產生 player_number、狀態依輸入設定 */
    public function createPlayer(StoreRequestData $data): Player
    {
        return $this->create([
            'player_number' => $this->repository->generateUniquePlayerNumber(),
            'name' => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
            'password' => $data->password,
            'status' => $data->status->value,
        ]);
    }

    /** 更新玩家資料；密碼有值才更新；player_number 不可變更 */
    public function updatePlayer(Player $player, UpdateRequestData $data): void
    {
        $fields = [
            'name' => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
            'status' => $data->status->value,
        ];

        if (!is_null($data->password)) {
            $fields['password'] = $data->password;
        }

        $player->update($fields);
    }

    /** 刪除玩家（軟刪除）；僅停用狀態可刪，啟用中拋 422 */
    public function deletePlayer(Player $player): void
    {
        throw_if(
            condition: Status::SUSPENDED !== $player->status,
            exception: new UnprocessableException(
                trans('api-codes.' . ApiCode::PLAYER_NOT_SUSPENDED->value),
                ApiCode::PLAYER_NOT_SUSPENDED,
            ),
        );

        $player->delete();
    }

    protected function getProxyRepository(): RepositoryInterface
    {
        return $this->repository;
    }
}
