<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_requests', function (Blueprint $table) {
            $table->id();

            // ユーザ
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');

            // 種類（遅刻・早退・欠勤）
            $table->enum('type', ['late', 'early_leave', 'absence']);

            // 対象日
            $table->date('target_date');

            // 時刻（遅刻・早退のみ）
            $table->time('request_time')->nullable();

            // 理由
            $table->text('reason');

            // ステータス
            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('pending');

            // 承認者
            $table->foreignId('approver_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // 承認日時
            $table->dateTime('approved_at')->nullable();

            // 管理者コメント
            $table->text('admin_comment')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'target_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_requests');
    }
};