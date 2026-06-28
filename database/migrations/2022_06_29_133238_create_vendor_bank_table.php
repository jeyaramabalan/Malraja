<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorBankTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_bank', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('ifsc');
            $table->string('branch')->default("");
            $table->string('account_type')->default("savings");
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
        Schema::dropIfExists('vendor_bank');
    }
}
