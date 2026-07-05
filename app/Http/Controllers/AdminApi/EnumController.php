<?php

namespace App\Http\Controllers\AdminApi;

use App\Enums\Admin\Status as AdminStatus;
use App\Enums\Announcement\Status as AnnouncementStatus;
use App\Enums\Announcement\TargetAudience;
use App\Enums\Item\Status as ItemStatus;
use App\Enums\Permission\Name;
use App\Enums\Player\Status as PlayerStatus;
use App\Enums\Product\Status as ProductStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class EnumController extends Controller
{
    /** 固定選項值與文字的對應表 - 權限 */
    public function permission(): JsonResponse
    {
        return $this->success([
            Name::SWAGGER_API_ENUM_PROPERTY => Name::SWAGGER_API_ENUM_OPTIONS,
        ]);
    }

    /** 固定選項值與文字的對應表 - 管理員狀態 */
    public function adminStatus(): JsonResponse
    {
        return $this->success([
            AdminStatus::SWAGGER_API_ENUM_PROPERTY => AdminStatus::SWAGGER_API_ENUM_OPTIONS,
        ]);
    }

    /** 固定選項值與文字的對應表 - 公告狀態 */
    public function announcementStatus(): JsonResponse
    {
        return $this->success([
            AnnouncementStatus::SWAGGER_API_ENUM_PROPERTY => AnnouncementStatus::SWAGGER_API_ENUM_OPTIONS,
        ]);
    }

    /** 固定選項值與文字的對應表 - 公告目標對象 */
    public function announcementTargetAudience(): JsonResponse
    {
        return $this->success([
            TargetAudience::SWAGGER_API_ENUM_PROPERTY => TargetAudience::SWAGGER_API_ENUM_OPTIONS,
        ]);
    }

    /** 固定選項值與文字的對應表 - 玩家狀態 */
    public function playerStatus(): JsonResponse
    {
        return $this->success([
            PlayerStatus::SWAGGER_API_ENUM_PROPERTY => PlayerStatus::SWAGGER_API_ENUM_OPTIONS,
        ]);
    }

    /** 固定選項值與文字的對應表 - 商品狀態 */
    public function productStatus(): JsonResponse
    {
        return $this->success([
            ProductStatus::SWAGGER_API_ENUM_PROPERTY => ProductStatus::SWAGGER_API_ENUM_OPTIONS,
        ]);
    }

    /** 固定選項值與文字的對應表 - 道具啟用狀態 */
    public function itemStatus(): JsonResponse
    {
        return $this->success([
            ItemStatus::SWAGGER_API_ENUM_PROPERTY => ItemStatus::SWAGGER_API_ENUM_OPTIONS,
        ]);
    }
}
