<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->string('bill_id');
            $table->integer('customer_id');
            $table->string('date')->default(Carbon::today());
            $table->string('payment_method')->default('Cash');
            $table->integer('created_by');
            $table->string('order_discount')->default('0');
            $table->string('total');
            $table->string('return_total')->default(0);
            $table->string('status')->default('pending');
            $table->tinyInteger('return_status')->default(0);
            $table->tinyInteger('damage_status')->default(0);
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
        Schema::dropIfExists('order');
    }
}
