<?php

namespace App\Docs\AdminApi;

use OpenApi\Attributes as OA;

/**
 * 後台 API 的 Tag 群組定義。
 *
 * 所有後台 Routes/ 裡用到的 tags 名稱都必須先在此宣告，
 * Swagger UI 才會顯示對應的群組說明文字。
 * 新增後台 API 群組時，在此追加一個 #[OA\Tag(...)] 即可。
 */
class Tags {}
