<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('purchase');
            $table->string('bill');
            $table->date('date');
            $table->integer('product_id');
            $table->integer('category_id');
            $table->integer('hsn_id');
            $table->string('sale')->default('0');
            $table->string('purchase')->default('0');
            $table->string('sale_return')->default('0');
            $table->string('purchase_return')->default('0');
            $table->string('purchase_free')->default('0');
            $table->string('sale_free')->default('0');
            $table->string('sale_damage')->default('0');
            $table->string('purchase_damage')->default('0');
            $table->string('purchase_damage_return')->default('0');
            $table->string('sale_damage_return')->default('0');
            $table->timestamps();
        });
    }

    // $qs1 = "INSERT INTO `tbl_stock`( `var_type`, `billno`, `date`,`var_pcode`, `var_pcate`, `var_pname`, `var_hsn`,  `var_salstock`, `var_givefree` ) VALUES ('sale','$var_createbillid','$var_billdate','$pcode','$cat','$productID','$hsn','$count','$free')";
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock');
    }
}
