<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dakoku_requests', function (Blueprint $table) {
            $table->boolean('auto_break_out')
                ->default(false)
                ->after('requested_punch_out');
        });
    }

    public function down(): void
    {
        Schema::table('dakoku_requests', function (Blueprint $table) {
            $table->dropColumn('auto_break_out');
        });
    }
};