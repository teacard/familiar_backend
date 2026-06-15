<?php

namespace App\Docs\AdminApi;

use OpenApi\Attributes as OA;

/**
 * 後台 API 的 OpenAPI 規格定義。
 *
 * 定義 API 文件的基本資訊、Server 環境清單與認證方式（Sanctum Bearer Token）。
 * 此類別不含任何邏輯，純粹作為 Attribute 的掛載點供 l5-swagger 掃描。
 *
 * 對應 l5-swagger config 的 'admin-api' 群組，產生的文件路由為 /admin-api/swagger。
 */
#[OA\Info(
    version: '1.0.0',
    description: '',
    title: '後台 API 說明文件',
)]
#[OA\Server(
    url: L5_SWAGGER_ADMIN_API_URL,
    description: '當前環境',
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    description: '身份認證金鑰',
    scheme: 'bearer',
)]
class OpenApiSpec
{
}
