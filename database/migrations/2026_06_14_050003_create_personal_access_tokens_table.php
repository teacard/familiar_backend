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
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->comment('個人存取 Token 資料表');
            $table->id();
            $table->morphs('tokenable');
            $table->text('name')->comment('Token 名稱');
            $table->string('token', 64)->unique()->comment('SHA-256 雜湊後的 Token');
            $table->text('abilities')->nullable()->comment('Token 可用能力（JSON）');
            // $table->timestamp('last_used_at')->nullable()->comment('最後使用時間');
            $table->timestamp('expires_at')->nullable()->index()->comment('Token 過期時間');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
