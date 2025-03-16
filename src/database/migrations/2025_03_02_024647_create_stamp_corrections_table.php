<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('stamp_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ユーザーID
            $table->date('target_date'); // 申請対象の日付
            $table->string('reason'); // 申請理由
            $table->string('status')->default('承認待ち'); // 承認待ち、承認済み、却下などのステータス
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stamp_corrections');
    }
};