<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleSaleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_sale', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('article_id');
            $table->integer('sale_id');
            $table->integer('amount');
            $table->integer('iva_id')->nullable();
            // $table->enum('measurement', ['gramo', 'kilo'])->nullable();
            // $table->enum('measurement_original', ['gramo', 'kilo'])->nullable();
            $table->decimal('cost')->nullable();
            $table->decimal('price')->nullable();
            $table->decimal('with_dolar')->nullable();

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
        Schema::dropIfExists('article_sale');
    }
}
