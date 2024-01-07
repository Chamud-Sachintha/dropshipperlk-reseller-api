<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reseller_id');
            $table->string('product_id');
            $table->string('order');
            $table->string('name');
            $table->string('address');
            $table->string('city');
            $table->string('district');
            $table->string('contact_1');
            $table->string('contact_2');
            $table->integer('quantity');
            $table->float('total_amount');
            $table->integer('payment_method');
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
        Schema::dropIfExists('orders');
    }
}
