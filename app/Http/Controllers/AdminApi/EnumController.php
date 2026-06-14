<?php

namespace App\Http\Controllers\AdminApi;

use App\Data\All\Response\EnumOptionResponse;
use App\Enums\Permission\Name;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class EnumController extends Controller
{
    /** 固定選項值與文字的對應表 - 權限 */
    public function permission(): JsonResponse
    {
        return $this->success([
            Name::SWAGGER_API_ENUM_PROPERTY => EnumOptionResponse::collection(Name::swaggerApiEnumOptions()),
        ]);
    }
}
