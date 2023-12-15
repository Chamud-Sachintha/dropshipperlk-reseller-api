<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResellersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resellers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('b_name');
            $table->string('address');
            $table->string('phone_number');
            $table->string('nic_number');
            $table->string('email');
            $table->string('password');
            $table->string('token')->nullable();
            $table->integer('login_time')->nullable();
            $table->string('ref_code');
            $table->string('code');
            $table->integer('create_time');
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
        Schema::dropIfExists('resellers');
    }
}
