<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('grade_contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maker_name_id');
            $table->unsignedBigInteger('model_name_id');
            $table->unsignedBigInteger('grade_name_id');
            $table->longText('grade_text_content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_contents');
    }
};
