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
        Schema::table('shifts', function (Blueprint $table) {
            // 退勤時間（end_time）の後ろに、休憩時間（分単位）のカラムを追加
            // デフォルト値を 0 にすることで、既存のレコードがエラーになるのを防ぎます
            $table->integer('break_minutes')->default(0)->after('end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            // ロールバック（元に戻す）時にカラムを削除
            $table->dropColumn('break_minutes');
        });
    }
};