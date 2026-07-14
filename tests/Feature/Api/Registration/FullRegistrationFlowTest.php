<?php

namespace Tests\Feature\Api\Registration;

use App\Enums\Media\CollectionName;
use App\Mail\PlayerRegistrationVerificationCode;
use App\Models\DraftPlayer;
use App\Models\Player;
use Database\Seeders\TemporaryMediaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FullRegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    /** 全新 email 走完 Step①→②-A→②-B→③-A→③-B→④，正式 players 資料正確建立 */
    public function testCompletesFullRegistrationFlow(): void
    {
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        Mail::fake();
        $this->seed(TemporaryMediaSeeder::class);

        // Step① 提交 email
        $storeResponse = $this->postJson('/api/registrations', ['email' => 'full-flow@example.com']);
        $storeResponse->assertOk();
        $token = $storeResponse->json('data.token');

        // Step②-A 寄送驗證碼
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code')
            ->assertOk();
        Mail::assertQueued(PlayerRegistrationVerificationCode::class);
        $code = DraftPlayer::query()->first()->verification_code;

        // Step②-B 驗證驗證碼
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code/verify', ['code' => $code])
            ->assertOk();

        // Step③-A 上傳大頭照
        $uploadResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('/api/registrations/profile/avatar', ['avatar' => UploadedFile::fake()->image('avatar.jpg')]);
        $uploadResponse->assertOk();
        $mediaId = $uploadResponse->json('data.id');

        // Step③-B 送出暱稱與大頭照
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/profile', ['name' => '生物蒐集家', 'mediaId' => $mediaId])
            ->assertOk();

        // Step④ 設定密碼，完成註冊
        $completeResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/password', [
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
            ]);
        $completeResponse->assertOk();

        // THEN  正式 players 資料正確建立，player_number 格式正確，大頭照正確移轉，草稿被刪除
        $player = Player::query()->where('email', 'full-flow@example.com')->first();
        $this->assertNotNull($player);
        $this->assertSame('生物蒐集家', $player->name);
        $this->assertMatchesRegularExpression('/^PL\d{8}$/', $player->player_number);
        $this->assertSame('active', $player->status->value);
        $this->assertNotNull($player->getFirstMedia(CollectionName::PLAYER->value));
        $this->assertDatabaseMissing('media', ['id' => $mediaId]);
        $this->assertDatabaseCount('draft_players', 0);

        // AND   舊 token 已隨草稿刪除而失效
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/registrations')
            ->assertUnprocessable();
    }
}
