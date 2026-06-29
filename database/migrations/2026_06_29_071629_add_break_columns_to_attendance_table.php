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
            Schema::table('attendance', function (Blueprint $table) {
                $table->dateTime('break_start')->nullable();
                $table->dateTime('break_end')->nullable();
            });
        }

        public function down(): void
        {
            Schema::table('attendance', function (Blueprint $table) {
                $table->dropColumn(['break_start', 'break_end']);
            });
        }
};
