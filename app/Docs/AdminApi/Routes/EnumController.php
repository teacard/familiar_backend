<?php

namespace App\Docs\AdminApi\Routes;

use App\Docs\AdminApi\ResponseContents\Enum\AdminStatusResponseContent;
use App\Docs\AdminApi\ResponseContents\Enum\AnnouncementStatusResponseContent;
use App\Docs\AdminApi\ResponseContents\Enum\AnnouncementTargetAudienceResponseContent;
use App\Docs\AdminApi\ResponseContents\Enum\EnumResponseContent;
use App\Docs\AdminApi\ResponseContents\Enum\ItemStatusResponseContent;
use App\Docs\AdminApi\ResponseContents\Enum\PlayerStatusResponseContent;
use App\Docs\AdminApi\ResponseContents\Enum\ProductStatusResponseContent;
use App\Docs\All\Responses\OkResponse;
use OpenApi\Attributes as OA;

class EnumController
{
    #[OA\Get(
        path: '/enums/permission',
        operationId: 'admin-api.enums.permission',
        summary: '取得權限 Enum 選項對應表',
        tags: ['Enum'],
        responses: [
            new OkResponse(contentRef: EnumResponseContent::class),
        ],
    )]
    /** 取得權限 Enum 選項對應表 */
    public function permission(): void
    {
    }

    #[OA\Get(
        path: '/enums/admin-status',
        operationId: 'admin-api.enums.admin-status',
        summary: '取得管理員狀態 Enum 選項對應表',
        tags: ['Enum'],
        responses: [
            new OkResponse(contentRef: AdminStatusResponseContent::class),
        ],
    )]
    /** 取得管理員狀態 Enum 選項對應表 */
    public function adminStatus(): void
    {
    }

    #[OA\Get(
        path: '/enums/announcement-status',
        operationId: 'admin-api.enums.announcement-status',
        summary: '取得公告狀態 Enum 選項對應表',
        tags: ['Enum'],
        responses: [
            new OkResponse(contentRef: AnnouncementStatusResponseContent::class),
        ],
    )]
    /** 取得公告狀態 Enum 選項對應表 */
    public function announcementStatus(): void
    {
    }

    #[OA\Get(
        path: '/enums/announcement-target-audience',
        operationId: 'admin-api.enums.announcement-target-audience',
        summary: '取得公告目標對象 Enum 選項對應表',
        tags: ['Enum'],
        responses: [
            new OkResponse(contentRef: AnnouncementTargetAudienceResponseContent::class),
        ],
    )]
    /** 取得公告目標對象 Enum 選項對應表 */
    public function announcementTargetAudience(): void
    {
    }

    #[OA\Get(
        path: '/enums/player-status',
        operationId: 'admin-api.enums.player-status',
        summary: '取得玩家狀態 Enum 選項對應表',
        tags: ['Enum'],
        responses: [
            new OkResponse(contentRef: PlayerStatusResponseContent::class),
        ],
    )]
    /** 取得玩家狀態 Enum 選項對應表 */
    public function playerStatus(): void
    {
    }

    #[OA\Get(
        path: '/enums/product-status',
        operationId: 'admin-api.enums.product-status',
        summary: '取得商品狀態 Enum 選項對應表',
        tags: ['Enum'],
        responses: [
            new OkResponse(contentRef: ProductStatusResponseContent::class),
        ],
    )]
    /** 取得商品狀態 Enum 選項對應表 */
    public function productStatus(): void
    {
    }

    #[OA\Get(
        path: '/enums/item-status',
        operationId: 'admin-api.enums.item-status',
        summary: '取得道具啟用狀態 Enum 選項對應表',
        tags: ['Enum'],
        responses: [
            new OkResponse(contentRef: ItemStatusResponseContent::class),
        ],
    )]
    /** 取得道具啟用狀態 Enum 選項對應表 */
    public function itemStatus(): void
    {
    }
}
