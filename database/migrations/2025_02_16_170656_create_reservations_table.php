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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedBigInteger('table_id');
            $table->foreign('table_id')->references('id')->on('table_seats')->cascadeOnUpdate()->cascadeOnDelete();
            $table->dateTime('datetime');
            $table->string('info')->nullable();
            $table->string('verification_code', 6)->nullable();
            $table->enum('status', ['ongoing', 'seated', 'done', 'no_show', 'canceled'])->default('ongoing');
            $table->timestamp('warning_sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
