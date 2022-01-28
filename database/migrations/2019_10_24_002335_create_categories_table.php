<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            $table->string('name', 128);
            $table->string('title', 128)->nullable();
            $table->integer('icon_id')->unsigned()->nullable();
            $table->string('image_url', 128)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('user_id')->unsigned();

            $table->foreign('user_id')
                    ->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
}
