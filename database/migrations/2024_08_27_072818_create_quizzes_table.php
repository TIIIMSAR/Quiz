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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')
            ->constrained()
            ->onDelete('cascade')
            ->onUpdate('cascade');

            $table->string('title');
            $table->string('summary');
            $table->string('url_quiz');
            $table->tinyInteger('score');
            $table->tinyInteger('published');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
