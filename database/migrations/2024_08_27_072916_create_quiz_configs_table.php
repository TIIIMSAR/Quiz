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
        Schema::create('quiz_configs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
            ->constrained()
            ->onDelete('cascade')
            ->onUpdate('cascade');

            $table->foreignId('quiz_id')
            ->constrained()
            ->onDelete('cascade')
            ->onUpdate('cascade');

            $table->tinyInteger('number_question');
            $table->tinyInteger('level');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_configs');
    }
};
