<?php

namespace App\Services;

use App\Data\AdminApi\Request\Announcement\IndexRequestData;
use App\Data\AdminApi\Request\Announcement\StoreRequestData;
use App\Data\AdminApi\Request\Announcement\UpdateRequestData;
use App\Enums\Announcement\Status;
use App\Enums\ApiCode;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnprocessableException;
use App\Models\Announcement;
use App\Repositories\Applications\Announcement\AnnouncementRepository;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Traits\AsRepositoryProxy;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AnnouncementService
{
    use AsRepositoryProxy;

    public function __construct(
        protected AnnouncementRepository $repository,
    ) {
    }

    /** 後台公告列表（依建立時間新到舊） */
    public function listAnnouncements(IndexRequestData $data): LengthAwarePaginator
    {
        $filters = [
            'keyword' => $data->keyword,
            'status' => $data->status?->value,
            'publishAtStart' => $data->publishAtStart,
            'publishAtEnd' => $data->publishAtEnd,
            'orderByDesc' => ['created_at', 'id'],
        ];

        // 置頂 → order_by < TOP_LIMIT；未置頂 → order_by >= TOP_LIMIT
        if (!is_null($data->pinned)) {
            $key = $data->pinned ? 'orderByLt' : 'orderByGte';
            $filters[$key] = Announcement::TOP_LIMIT;
        }

        return $this->paginate(
            perPage: $data->perPage,
            page: $data->page,
            filters: $filters,
        );
    }

    /** 依 id 查詢，找不到拋 404 */
    public function findOrFail(int $id): Announcement
    {
        /** @var Announcement|null $announcement */
        $announcement = $this->findById($id);

        throw_unless(
            condition: $announcement,
            exception: NotFoundException::class,
        );

        return $announcement;
    }

    /** 建立公告（自動判斷狀態，依 shouldPin 決定 order_by 後單次新增） */
    public function createAnnouncement(StoreRequestData $data): Announcement
    {
        $orderBy = $data->shouldPin
            ? $this->nextPinnedOrder()
            : $this->nextNonPinnedOrder();

        return $this->create([
            'title' => $data->title,
            'content' => $data->content,
            'target_audience' => $data->targetAudience->value,
            'publish_at' => $data->publishAt,
            'expires_at' => $data->expiresAt,
            'status' => Status::SCHEDULED->value,
            'order_by' => $orderBy,
        ]);
    }

    /** 更新公告（已發佈狀態禁改 publish_at/expires_at/target_audience） */
    public function updateAnnouncement(Announcement $announcement, UpdateRequestData $data): void
    {
        $immutableChanged = $announcement->target_audience !== $data->targetAudience
            || !$announcement->publish_at->equalTo($data->publishAt)
            || $announcement->expires_at?->toDateTimeString() !== $data->expiresAt;

        throw_if(
            condition: Status::PUBLISHED === $announcement->status && $immutableChanged,
            exception: new UnprocessableException(
                trans('api-codes.' . ApiCode::ANNOUNCEMENT_PUBLISHED_IMMUTABLE->value),
                ApiCode::ANNOUNCEMENT_PUBLISHED_IMMUTABLE,
            ),
        );

        DB::transaction(function () use ($announcement, $data) {
            $fields = [
                'title' => $data->title,
                'content' => $data->content,
                'target_audience' => $data->targetAudience->value,
                'publish_at' => $data->publishAt,
                'expires_at' => $data->expiresAt,
            ];

            $pinChanged = $data->shouldPin !== $announcement->is_pinned;
            $previousOrder = $announcement->order_by;

            if ($pinChanged) {
                $fields['order_by'] = $data->shouldPin
                    ? $this->nextPinnedOrder()
                    : $this->nextNonPinnedOrder();
            }

            $announcement->update($fields);

            // 取消置頂時，把原位置後方的置頂往前補位，維持 0/1/2 連續
            if ($pinChanged && !$data->shouldPin) {
                $this->decrement(
                    filters: [
                        'orderByLt' => Announcement::TOP_LIMIT,
                        'orderByGt' => $previousOrder,
                    ],
                    column: 'order_by',
                );
            }
        });
    }

    /** 刪除公告（已發佈狀態禁刪） */
    public function deleteAnnouncement(Announcement $announcement): void
    {
        throw_if(
            condition: Status::PUBLISHED === $announcement->status,
            exception: new UnprocessableException(
                trans('api-codes.' . ApiCode::ANNOUNCEMENT_PUBLISHED_UNDELETABLE->value),
                ApiCode::ANNOUNCEMENT_PUBLISHED_UNDELETABLE,
            ),
        );

        $announcement->delete();
    }

    protected function getProxyRepository(): RepositoryInterface
    {
        return $this->repository;
    }

    /** 取得下一個置頂 order_by（= 目前置頂數）；已達 TOP_LIMIT 則拋 422。 */
    private function nextPinnedOrder(): int
    {
        $pinnedCount = $this->count(['orderByLt' => Announcement::TOP_LIMIT]);

        throw_if(
            condition: Announcement::TOP_LIMIT === $pinnedCount,
            exception: new UnprocessableException(
                trans('api-codes.' . ApiCode::ANNOUNCEMENT_PIN_LIMIT_REACHED->value),
                ApiCode::ANNOUNCEMENT_PIN_LIMIT_REACHED,
            ),
        );

        return $pinnedCount;
    }

    /** 取得下一個非置頂 order_by：目前非置頂最大值 +1，無則為 UNPINNED_ORDER。 */
    private function nextNonPinnedOrder(): int
    {
        /** @var Announcement|null $last */
        $last = $this->first([
            'orderByGte' => Announcement::TOP_LIMIT,
            'orderByDesc' => 'order_by',
        ]);

        return is_null($last)
            ? Announcement::UNPINNED_ORDER
            : $last->order_by + 1;
    }
}
