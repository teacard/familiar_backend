<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->comment('使用者資料表');
            $table->id();
            $table->string('name')->comment('使用者名稱');
            $table->string('email')->unique()->comment('電子郵件（登入帳號）');
            $table->timestamp('email_verified_at')->nullable()->comment('信箱驗證時間');
            $table->string('password')->comment('bcrypt 雜湊密碼');
            $table->rememberToken();
            $table->timestamps();
        });

        // Schema::create('password_reset_tokens', function (Blueprint $table) {
        //     $table->comment('密碼重設 Token 資料表');
        //     $table->string('email')->primary()->comment('使用者電子郵件');
        //     $table->string('token')->comment('重設 Token');
        //     $table->timestamp('created_at')->nullable()->comment('建立時間');
        // });

        // Schema::create('sessions', function (Blueprint $table) {
        //     $table->comment('使用者 Session 資料表');
        //     $table->string('id')->primary()->comment('Session ID');
        //     $table->foreignId('user_id')->nullable()->index()->comment('關聯使用者 ID（可為空，支援訪客）');
        //     $table->string('ip_address', 45)->nullable()->comment('登入 IP 位址');
        //     $table->text('user_agent')->nullable()->comment('瀏覽器 User-Agent');
        //     $table->longText('payload')->comment('Session 資料序列化內容');
        //     $table->integer('last_activity')->index()->comment('最後活動時間（Unix timestamp）');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        // Schema::dropIfExists('password_reset_tokens');
        // Schema::dropIfExists('sessions');
    }
};
