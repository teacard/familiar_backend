<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_rewards', function (Blueprint $table) {
            $table->comment('商品內容物明細');
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->comment('對應道具表（items）的目標 ID；金幣/鑽石/通行證等皆統一視為一種道具，故不再區分獎勵種類；一律必填');
            $table->unsignedInteger('quantity')->default(1)->comment('數量，語意依道具本身定義而定（例如消耗品數量、貨幣點數、通行證天數）');
            $table->timestamps();

            $table->index('product_id', 'idx_product_rewards_product_id');
        });

        if ('pgsql' === DB::connection()->getDriverName()) {
            DB::statement("COMMENT ON INDEX idx_product_rewards_product_id IS '加速依商品 ID 撈取其獎勵明細（商品詳情頁 eager load rewards）'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_rewards');
    }
};
