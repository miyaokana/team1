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
        Schema::create('dakoku_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('requested_punch_in')->nullable();  // 修正希望の出勤時間
            $table->time('requested_punch_out')->nullable(); // 修正希望の退勤時間
            $table->string('reason');                        // 申請理由
            // 状態管理：pending(申請中), approved(承認済), rejected(却下)
            $table->string('status')->default('pending');    
            $table->text('admin_comment')->nullable();       // 管理者からのコメント
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dakoku_requests');
    }
};
