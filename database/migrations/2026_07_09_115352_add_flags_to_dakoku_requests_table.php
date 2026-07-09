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
        Schema::table('dakoku_requests', function (Blueprint $table) {
            // 💡 申請の種類（出勤か退勤か、および削除申請か）を判別するフラグを追加
            $table->boolean('is_in_request')->default(false)->after('date');
            $table->boolean('is_out_request')->default(false)->after('is_in_request');
            $table->boolean('is_delete')->default(false)->after('is_out_request');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dakoku_requests', function (Blueprint $table) {
            $table->dropColumn(['is_in_request', 'is_out_request', 'is_delete']);
        });
    }
};