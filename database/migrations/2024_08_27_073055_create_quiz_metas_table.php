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
        Schema::create('quiz_metas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quiz_id')
            ->constrained()
            ->onDelete('cascade')
            ->onUpdate('cascade');

            $table->string('key');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_metas');
    }
};
