<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('user_id')->unsigned();
            $table->integer('sale_type_id')->unsigned()->nullable();
            $table->integer('num_sale');
            $table->decimal('percentage_card')->nullable();
            $table->integer('client_id')->nullable()->unsigned();
            $table->integer('buyer_id')->nullable()->unsigned();
            $table->integer('special_price_id')->nullable()->unsigned();
            $table->decimal('debt')->nullable();

            $table->foreign('user_id')
                    ->references('id')->on('users');
            $table->foreign('client_id')
                    ->references('id')->on('clients');
            $table->foreign('special_price_id')
                    ->references('id')->on('special_prices');

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
        Schema::dropIfExists('sales');
    }
}
