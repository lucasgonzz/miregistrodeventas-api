<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->bigIncrements('id');

            // $table->integer('user_id')->unsigned();
            $table->integer('article_id')->unsigned();
            $table->string('url', 128);
            $table->boolean('first')->default(false);

            // $table->foreign('user_id')
            //         ->references('id')->on('users');
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
        Schema::dropIfExists('images');
    }
}
