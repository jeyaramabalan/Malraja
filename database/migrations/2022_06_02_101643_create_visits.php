<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->integer('customer');
            $table->integer('user_id');
            $table->string('accompany_list_id')->default('')->comment('multiple user id');
            $table->string('campaign_name')->default('');
            $table->boolean('follow_up_needed')->default(false);
            $table->string('product_list_id')->comment('multiple product id');
            $table->integer('purpose_of_visit_id');
            $table->string('quantity');
            $table->string('remarks')->default('');
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
        Schema::dropIfExists('visits');
    }
}
