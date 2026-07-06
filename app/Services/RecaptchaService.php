<?php

namespace App\Services;

use App\Enums\ApiCode;
use App\Exceptions\UnprocessableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    /**
     * 向 Google 驗證 reCAPTCHA v3 token；success、score、action 任一不符即拋例外。
     * $action 由呼叫端指定（如 'admin_login'、'player_login'），確保不同表單的 token 不能互相挪用。
     *
     * @throws UnprocessableException
     */
    public function verify(string $token, ?string $ip, string $action): void
    {
        try {
            $response = Http::asForm()
                ->timeout((int)config('services.recaptcha.timeout'))
                ->post(config('services.recaptcha.verify_url'), [
                    'secret' => config('services.recaptcha.secret_key'),
                    'response' => $token,
                    'remoteip' => $ip,
                ]);
        } catch (ConnectionException) {
            throw $this->failedException();
        }

        $success = true === $response->json('success');
        $score = (float)$response->json('score', 0);
        $responseAction = $response->json('action');
        $threshold = (float)config('services.recaptcha.score_threshold');

        if (!$response->successful() || !$success || $score < $threshold || $action !== $responseAction) {
            throw $this->failedException();
        }
    }

    private function failedException(): UnprocessableException
    {
        return new UnprocessableException(
            trans('api-codes.' . ApiCode::RECAPTCHA_VERIFICATION_FAILED->value),
            ApiCode::RECAPTCHA_VERIFICATION_FAILED,
        );
    }
}
