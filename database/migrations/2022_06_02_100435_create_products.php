<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->string("name");
            $table->string("code");
            $table->string("hsn");
            $table->integer("category_id");
            $table->string("unit");
            $table->string("mrp");
            $table->string("customer_rate");
            $table->string("purchase_rate");
            $table->string("gst");
            $table->string("sgt");
            $table->string("additional_tax");
            $table->string("final_price");
            $table->tinyInteger("status")->default(1);
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
        Schema::dropIfExists('products');
    }
}
