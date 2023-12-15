<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKYCInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('k_y_c_information', function (Blueprint $table) {
            $table->id();
            $table->string('client_id');
            $table->string('front_image_nic');
            $table->string('back_image_nic');
            $table->string('status');
            $table->integer('create_time');
            $table->integer('mod_time');
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
        Schema::dropIfExists('k_y_c_information');
    }
}
