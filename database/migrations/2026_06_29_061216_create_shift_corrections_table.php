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
        Schema::create('shift_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')
                ->constrained('attendance')
                ->onDelete('cascade');
            $table->dateTime('before_check_in')->nullable();
            $table->dateTime('before_check_out')->nullable();
            $table->dateTime('after_check_in')->nullable();
            $table->dateTime('after_check_out')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_corrections');
    }
};
