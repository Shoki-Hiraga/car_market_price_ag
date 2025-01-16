<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sc_goo_model', function (Blueprint $table) {
            $table->id(); // 自動増分ID
            $table->unsignedBigInteger('maker_name_id'); // 外部キーとしてのmaker_name_id
            $table->string('model_name', 255); // モデル名
            $table->timestamps(); // created_at, updated_at

            // 外部キー制約
            $table->foreign('maker_name_id')
                ->references('id')
                ->on('sc_goo_maker')
                ->onDelete('cascade');

            // 一意制約
            $table->unique(['maker_name_id', 'model_name'], 'unique_maker_model');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_goo_model');
    }
};
