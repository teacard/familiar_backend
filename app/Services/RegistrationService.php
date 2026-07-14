<?php

namespace App\Services;

use App\Data\Api\Request\Registration\PasswordRequestData;
use App\Data\Api\Request\Registration\ProfileRequestData;
use App\Data\Api\Request\Registration\StoreRequestData;
use App\Data\Api\Request\Registration\VerifyCodeRequestData;
use App\Enums\ApiCode;
use App\Enums\DraftPlayer\Step;
use App\Enums\Media\CollectionName;
use App\Enums\Player\Status;
use App\Exceptions\UnprocessableException;
use App\Mail\PlayerRegistrationVerificationCode;
use App\Models\DraftPlayer;
use App\Models\Player;
use App\Repositories\Applications\DraftPlayer\DraftPlayerRepository;
use App\Repositories\Applications\Player\PlayerRepository;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Traits\AsRepositoryProxy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegistrationService
{
    use AsRepositoryProxy;

    /** 驗證碼有效期限（分鐘），超過即視為過期 */
    private const int VERIFICATION_CODE_TTL_MINUTES = 5;
    /** 重寄驗證碼的冷卻秒數，未滿此秒數不可重寄 */
    private const int VERIFICATION_CODE_RESEND_COOLDOWN_SECONDS = 60;
    /** 驗證碼可錯誤嘗試的次數上限，達此次數即鎖定須重新索取 */
    private const int VERIFICATION_CODE_MAX_ATTEMPTS = 3;

    public function __construct(
        protected DraftPlayerRepository $repository,
        protected PlayerRepository $playerRepository,
        protected MediaService $mediaService,
    ) {
    }

    /**
     * Step1：提交 email，建立或延續草稿並換發 token（不寄信）。
     * token 格式為 `{draftId}|{secret}`（比照 Sanctum 的做法），`secret` 以 bcrypt 雜湊存入 `token` 欄位；
     * 之後每次請求只需帶這組完整 token，ResolveRegistrationDraft middleware 解析出 id 直接查主鍵，
     * 不需要額外帶 email。
     */
    public function submitEmail(StoreRequestData $data): string
    {
        throw_if(
            condition: $this->playerRepository->exists(['email' => $data->email]),
            exception: new UnprocessableException(
                trans('api-codes.' . ApiCode::EMAIL_ALREADY_REGISTERED->value),
                ApiCode::EMAIL_ALREADY_REGISTERED,
            ),
        );

        $secret = Str::random(40);

        /** @var DraftPlayer|null $draft */
        $draft = $this->first(['email' => $data->email]);

        if ($draft) {
            $draft->update(['token' => $secret]);

            return "{$draft->id}|{$secret}";
        }

        /** @var DraftPlayer $draft */
        $draft = $this->create([
            'email' => $data->email,
            'token' => $secret,
            'registration_step' => Step::EMAIL_SUBMITTED,
        ]);

        return "{$draft->id}|{$secret}";
    }

    /** Step2-A：寄送/重寄驗證碼（首次寄送與重寄共用）；$draft 已由 ResolveRegistrationDraft middleware 驗證過 email+token */
    public function sendVerificationCode(DraftPlayer $draft): void
    {
        if ($draft->verification_code_expires_at) {
            $lastSentAt = $draft->verification_code_expires_at->copy()->subMinutes(self::VERIFICATION_CODE_TTL_MINUTES);

            throw_if(
                condition: $lastSentAt->addSeconds(self::VERIFICATION_CODE_RESEND_COOLDOWN_SECONDS)->isFuture(),
                exception: new UnprocessableException(
                    trans('api-codes.' . ApiCode::VERIFICATION_CODE_RESEND_TOO_SOON->value),
                    ApiCode::VERIFICATION_CODE_RESEND_TOO_SOON,
                ),
            );
        }

        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $draft->update([
            'verification_code' => $code,
            'verification_code_expires_at' => now()->addMinutes(self::VERIFICATION_CODE_TTL_MINUTES),
            'verification_attempts' => 0,
        ]);

        Mail::to($draft->email)->queue(new PlayerRegistrationVerificationCode($code));
    }

    /** Step2-B：驗證驗證碼；$draft 已由 ResolveRegistrationDraft middleware 驗證過 email+token */
    public function verifyCode(DraftPlayer $draft, VerifyCodeRequestData $data): void
    {
        throw_if(
            condition: is_null($draft->verification_code),
            exception: new UnprocessableException(
                trans('api-codes.' . ApiCode::VERIFICATION_CODE_NOT_SENT->value),
                ApiCode::VERIFICATION_CODE_NOT_SENT,
            ),
        );

        throw_if(
            condition: $draft->verification_attempts >= self::VERIFICATION_CODE_MAX_ATTEMPTS,
            exception: new UnprocessableException(
                trans('api-codes.' . ApiCode::VERIFICATION_CODE_LOCKED->value),
                ApiCode::VERIFICATION_CODE_LOCKED,
            ),
        );

        throw_if(
            condition: $draft->verification_code_expires_at->isPast(),
            exception: new UnprocessableException(
                trans('api-codes.' . ApiCode::VERIFICATION_CODE_EXPIRED->value),
                ApiCode::VERIFICATION_CODE_EXPIRED,
            ),
        );

        if ($draft->verification_code !== $data->code) {
            $draft->increment('verification_attempts');

            throw new UnprocessableException(trans('api-codes.' . ApiCode::VERIFICATION_CODE_INVALID->value), ApiCode::VERIFICATION_CODE_INVALID);
        }

        if ($draft->registration_step->value < Step::CODE_VERIFIED->value) {
            $draft->update(['registration_step' => Step::CODE_VERIFIED]);
        }
    }

    /**
     * Step3：暱稱與大頭照；$draft 已由 ResolveRegistrationDraft middleware 驗證過 email+token。
     * 大頭照的 media 轉綁（transferToModel）比照既有 `AdminController::store()`/`update()` 的慣例，
     * 交由 Controller 在呼叫此方法後另外處理，不放在 Service 內。
     */
    public function updateProfile(DraftPlayer $draft, ProfileRequestData $data): void
    {
        throw_if(
            condition: $draft->registration_step->value < Step::CODE_VERIFIED->value,
            exception: new UnprocessableException(
                trans('api-codes.' . ApiCode::REGISTRATION_STEP_SKIPPED->value),
                ApiCode::REGISTRATION_STEP_SKIPPED,
            ),
        );

        $fields = ['name' => $data->name];

        if ($draft->registration_step->value < Step::PROFILE_COMPLETED->value) {
            $fields['registration_step'] = Step::PROFILE_COMPLETED;
        }

        $draft->update($fields);
    }

    /** Step4：設定密碼，完成註冊並寫入正式 players 表；$draft 已由 ResolveRegistrationDraft middleware 驗證過 email+token */
    public function complete(DraftPlayer $draft, PasswordRequestData $data): void
    {
        throw_if(
            condition: $draft->registration_step->value < Step::PROFILE_COMPLETED->value,
            exception: new UnprocessableException(
                trans('api-codes.' . ApiCode::REGISTRATION_STEP_SKIPPED->value),
                ApiCode::REGISTRATION_STEP_SKIPPED,
            ),
        );

        DB::transaction(function () use ($draft, $data): void {
            /** @var Player $player */
            $player = $this->playerRepository->create([
                'player_number' => $this->playerRepository->generateUniquePlayerNumber(),
                'name' => $draft->name,
                'email' => $draft->email,
                'password' => $data->password,
                'status' => Status::ACTIVE->value,
            ]);

            $draft->getFirstMedia(CollectionName::PLAYER->value)?->move($player, CollectionName::PLAYER->value);

            $draft->delete();
        });
    }

    protected function getProxyRepository(): RepositoryInterface
    {
        return $this->repository;
    }
}
