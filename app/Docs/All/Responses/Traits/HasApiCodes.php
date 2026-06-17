<?php

namespace App\Docs\All\Responses\Traits;

trait HasApiCodes
{
    private function suffixApiCodeAndMessages(
        string $description,
        array $apiCodeEnums = [],
    ): string {
        if (!empty($apiCodeEnums)) {
            $description = array_reduce(
                $apiCodeEnums,
                static function ($description, $apiCodeEnum) {
                    $apiCode  = $apiCodeEnum->value;
                    $message  = trans('api-codes.' . $apiCode);
                    $description .= "<br/><code>{$apiCode}</code>: {$message}";

                    return $description;
                },
                $description .= '，特殊的回應代碼與訊息：'
            );
        }

        return $description;
    }
}
