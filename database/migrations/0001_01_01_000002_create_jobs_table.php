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
        if ('sync' === config('queue.default')) {
            return;
        }

        Schema::create('jobs', function (Blueprint $table) {
            $table->comment('佇列任務資料表');
            $table->id();
            $table->string('queue')->index()->comment('佇列名稱');
            $table->longText('payload')->comment('任務序列化內容');
            $table->unsignedSmallInteger('attempts')->comment('嘗試執行次數');
            $table->unsignedInteger('reserved_at')->nullable()->comment('任務被取出時間（Unix timestamp）');
            $table->unsignedInteger('available_at')->comment('任務可執行時間（Unix timestamp）');
            $table->unsignedInteger('created_at')->comment('任務建立時間（Unix timestamp）');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->comment('批次任務資料表');
            $table->string('id')->primary()->comment('批次 ID');
            $table->string('name')->comment('批次名稱');
            $table->integer('total_jobs')->comment('批次總任務數');
            $table->integer('pending_jobs')->comment('待處理任務數');
            $table->integer('failed_jobs')->comment('失敗任務數');
            $table->longText('failed_job_ids')->comment('失敗任務 ID 列表');
            $table->mediumText('options')->nullable()->comment('批次選項');
            $table->integer('cancelled_at')->nullable()->comment('取消時間（Unix timestamp）');
            $table->integer('created_at')->comment('建立時間（Unix timestamp）');
            $table->integer('finished_at')->nullable()->comment('完成時間（Unix timestamp）');
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->comment('失敗任務紀錄資料表');
            $table->id();
            $table->string('uuid')->unique()->comment('任務唯一識別碼');
            $table->string('connection')->comment('佇列連線名稱');
            $table->string('queue')->comment('佇列名稱');
            $table->longText('payload')->comment('任務序列化內容');
            $table->longText('exception')->comment('失敗例外訊息');
            $table->timestamp('failed_at')->useCurrent()->comment('失敗時間');

            $table->index(['connection', 'queue', 'failed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ('sync' === config('queue.default')) {
            return;
        }

        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
