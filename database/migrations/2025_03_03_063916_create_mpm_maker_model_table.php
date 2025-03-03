<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mpm_maker', function (Blueprint $table) {
            $table->id(); // 自動インクリメントのプライマリキー
            $table->string('mpm_maker_name', 255);
            $table->unsignedBigInteger('maker_name_id')->unique();
            $table->timestamps();

            // maker_name_id にユニークキーを設定
            $table->unique('maker_name_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mpm_maker'); // シンプルにテーブルを削除
    }
};
