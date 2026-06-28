<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyExpense extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_expense', function (Blueprint $table) {
            $table->id();
            $table->integer('expense_id');
            $table->integer('expense_by');
            $table->integer('created_by');
            $table->string('amount');
            $table->date('date');
            $table->string('bill')->default('');
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('daily_expense');
    }
}
