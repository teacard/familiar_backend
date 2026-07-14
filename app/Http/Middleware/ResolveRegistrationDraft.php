<?php

namespace App\Http\Middleware;

use App\Enums\ApiCode;
use App\Exceptions\UnprocessableException;
use App\Models\DraftPlayer;
use App\Repositories\Applications\DraftPlayer\DraftPlayerRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

/**
 * 解析玩家自助註冊草稿：token 透過 `Authorization: Bearer {draftId}|{secret}` header 帶入
 * （比照 Sanctum 的做法，且不將這類等同密碼的 secret 暴露在 query string／request body，
 * 避免被 access log 記錄），解析出 id 直接查主鍵，再以 Hash::check() 比對 secret，通過後
 * 綁進 container，供 Controller action 直接以 `DraftPlayer $draft` 型別提示取用，Controller
 * 端不需要任何額外的取值程式碼。此 middleware 早於 FormRequest 驗證執行，故 header 尚未保證
 * 存在或為合法格式，需自行防呆，避免型別錯誤變成 500。
 */
class ResolveRegistrationDraft
{
    public function __construct(
        protected DraftPlayerRepository $repository,
    ) {
    }

    public function handle(Request $request, \Closure $next): Response
    {
        $token = $request->bearerToken();

        throw_if(
            condition: !is_string($token) || !str_contains($token, '|'),
            exception: new UnprocessableException(
                trans('api-codes.' . ApiCode::REGISTRATION_DRAFT_NOT_FOUND->value),
                ApiCode::REGISTRATION_DRAFT_NOT_FOUND,
            ),
        );

        [$id, $secret] = explode('|', $token, 2);

        throw_if(
            condition: !ctype_digit($id) || '' === $secret,
            exception: new UnprocessableException(
                trans('api-codes.' . ApiCode::REGISTRATION_DRAFT_NOT_FOUND->value),
                ApiCode::REGISTRATION_DRAFT_NOT_FOUND,
            ),
        );

        $draft = $this->repository->findById((int)$id);

        throw_if(
            condition: is_null($draft) || !Hash::check($secret, $draft->token),
            exception: new UnprocessableException(
                trans('api-codes.' . ApiCode::REGISTRATION_DRAFT_NOT_FOUND->value),
                ApiCode::REGISTRATION_DRAFT_NOT_FOUND,
            ),
        );

        app()->instance(DraftPlayer::class, $draft);

        return $next($request);
    }
}
