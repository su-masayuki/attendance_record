<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStampCorrectionBreaksTable extends Migration
{
    public function up()
    {
        Schema::create('stamp_correction_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stamp_correction_id')->constrained()->onDelete('cascade');
            $table->time('break_start');
            $table->time('break_end');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stamp_correction_breaks');
    }
}