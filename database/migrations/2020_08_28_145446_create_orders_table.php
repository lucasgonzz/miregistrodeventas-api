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
            $table->bigIncrements('id');

            $table->integer('buyer_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->enum('status', ['unconfirmed', 'canceled', 'confirmed', 'finished', 'delivered']);
            $table->boolean('deliver');
            $table->string('address')->nullable();
            $table->string('address_number')->nullable();
            $table->string('description')->nullable();
            $table->enum('payment_method', ['efectivo', 'tarjeta']);

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
