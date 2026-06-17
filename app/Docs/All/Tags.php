<?php

namespace App\Docs\All;

use OpenApi\Attributes as OA;

/**
 * 前後台共用的 Tag 群組定義。
 *
 * 前台（Default）與後台（AdminApi）都會用到的 tag 統一在此宣告，
 * 避免重複定義。各群組專屬的 tag 則放在各自的 Tags.php。
 */
#[OA\Tag(
    name: 'Enum',
    description: '固定選項的代碼與對應顯示文字',
)]
class Tags
{
}
