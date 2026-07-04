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
        Schema::create('products', function (Blueprint $table) {
            $table->comment('商品目錄');
            $table->id();
            $table->string('name', 50)->comment('商品名稱');
            $table->foreignId('product_type_id')->constrained()->comment('商品種類（對應 product_types.id，用於商城分類/篩選展示）');
            $table->unsignedInteger('amount')->comment('價格（新台幣，整數，單位：元）');
            $table->string('status', 20)->default('unpublished')->comment('上架狀態：published, unpublished（見 Product\Status enum）');
            $table->timestamps();

            $table->index(['status', 'product_type_id'], 'idx_products_status_product_type_id');
        });

        if ('pgsql' === DB::connection()->getDriverName()) {
            DB::statement("COMMENT ON INDEX idx_products_status_product_type_id IS '加速後台商品列表依上架狀態、種類篩選的查詢'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
