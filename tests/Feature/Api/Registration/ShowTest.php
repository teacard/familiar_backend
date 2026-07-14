<?php

namespace Tests\Feature\Api\Registration;

use App\Enums\DraftPlayer\Step;
use App\Enums\Media\CollectionName;
use App\Models\DraftPlayer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /** 帶入有效 token，回傳目前進度與已填內容，不含密碼相關欄位 */
    public function testReturnsCurrentProgress(): void
    {
        // GIVEN 已驗證信箱、填寫暱稱與大頭照的草稿
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $draft = DraftPlayer::query()->create([
            'email' => 'show-status@example.com',
            'token' => 'plain-secret',
            'name' => '生物蒐集家',
            'registration_step' => Step::PROFILE_COMPLETED,
        ]);
        $draft->addMedia(UploadedFile::fake()->image('avatar.jpg'))
            ->toMediaCollection(CollectionName::PLAYER->value);
        $token = $draft->id . '|plain-secret';

        // WHEN  查詢進度
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/registrations');

        // THEN  回傳目前進度、email、name、大頭照 URL，不含密碼相關欄位
        $response->assertOk()
            ->assertJsonPath('data.registrationStep', Step::PROFILE_COMPLETED->value)
            ->assertJsonPath('data.email', 'show-status@example.com')
            ->assertJsonPath('data.name', '生物蒐集家')
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.token');
        $this->assertNotEmpty($response->json('data.avatarUrl'));
    }

    /** 尚未填寫暱稱與大頭照時，對應欄位回傳 null，不報錯 */
    public function testReturnsNullForUnfilledFields(): void
    {
        // GIVEN 剛完成 Step1 的草稿(尚未有暱稱與大頭照)
        $response = $this->postJson('/api/registrations', ['email' => 'show-empty@example.com']);
        $token = $response->json('data.token');

        // WHEN  查詢進度
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/registrations');

        // THEN  name、avatarUrl 皆為 null
        $response->assertOk()
            ->assertJsonPath('data.name', null)
            ->assertJsonPath('data.avatarUrl', null);
    }
}
