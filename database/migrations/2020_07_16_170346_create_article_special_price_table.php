<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleSpecialPriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_special_price', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('special_price_id')->unsigned();
            $table->integer('article_id')->unsigned();
            $table->decimal('price')->nullable();
            // $table->timestamps();

            $table->foreign('special_price_id')
                    ->references('id')->on('special_prices');
            $table->foreign('article_id')
                    ->references('id')->on('articles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_special_price');
    }
}
