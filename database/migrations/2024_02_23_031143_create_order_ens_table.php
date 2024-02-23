<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderEnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_ens', function (Blueprint $table) {
            $table->id();
            $table->string('reseller_id');
            $table->string('order');
            $table->float('total_amount');
            $table->integer('payment_method');
            $table->string('bank_slip')->nullable();
            $table->integer('payment_status')->default(0);
            $table->integer('order_status')->default(0);
            $table->string('tracking_number')->nullable();
            $table->integer('is_reseller_completed')->default(0);
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
        Schema::dropIfExists('order_ens');
    }
}
