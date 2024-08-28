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
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
            ->constrained()
            ->onDelete('cascade')
            ->onUpdate('cascade');

            $table->string('content');
            $table->tinyInteger('level');
            $table->tinyInteger('score');
            $table->boolean('active')->nullable();
            $table->json('options');
            $table->string('file_path')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
