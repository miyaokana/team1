<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();

            // ユーザ
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');

            // ✅ 種類（enum化）
            $table->enum('type', ['paid', 'special']);

            // 日付範囲
            $table->date('start_date');
            $table->date('end_date');

            // ✅ 半日区分
            $table->enum('day_type', ['full_day', 'am', 'pm'])
                  ->default('full_day');

            // 理由
            $table->text('reason');

            // ✅ ステータス
            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('pending');

            // 承認者
            $table->foreignId('approver_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // 承認日時
            $table->dateTime('approved_at')->nullable();

            // 管理コメント
            $table->text('admin_comment')->nullable();

            $table->timestamps();

            // インデックス
            $table->index(['user_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};