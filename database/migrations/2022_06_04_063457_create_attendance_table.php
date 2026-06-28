<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('date', 32);
            $table->tinyInteger('check_in_status')->default(0);
            $table->string('check_in_latitude', 64);
            $table->string('check_in_longitude', 64);
            $table->string('check_out_latitude', 64);
            $table->string('check_out_longitude', 64);
            $table->string('check_in_time', 32);
            $table->string('check_out_time', 32);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance');
    }
}
