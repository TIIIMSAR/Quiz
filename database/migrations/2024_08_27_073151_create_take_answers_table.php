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
        Schema::create('take_answers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('take_id')
            ->constrained()
            ->onDelete('cascade')
            ->onUpdate('cascade');
                

            $table->json('answers')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('take_answers');
    }
};
