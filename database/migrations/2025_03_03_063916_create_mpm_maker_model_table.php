<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpm_maker_model', function (Blueprint $table) {
            $table->id(); // 自動インクリメントID
            $table->string('mpm_maker_name'); // メーカー名
            $table->unsignedBigInteger('maker_name_id')->index(); // メーカーID
            $table->string('mpm_model_name'); // モデル名
            $table->unsignedBigInteger('model_name_id')->index(); // モデルID
            $table->timestamps(); // created_at, updated_at の自動管理
            
            // ユニーク制約（同じ maker_name_id と model_name_id の重複を防ぐ）
            $table->unique(['maker_name_id', 'model_name_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mpm_maker_model');
    }
};
