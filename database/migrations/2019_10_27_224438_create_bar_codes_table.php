<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBarCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bar_codes', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->integer('amount');
            $table->integer('user_id')->unsigned();
            $table->integer('article_id')->unsigned()->nullable();

            $table->foreign('user_id')
                    ->references('id')->on('users');
            $table->foreign('article_id')
                    ->references('id')->on('articles');

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
        Schema::dropIfExists('bar_codes');
    }
}
