<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('bar_code', 128)->nullable();
            $table->string('name', 128)->nullable();
            $table->string('brand', 128)->nullable();
            $table->string('slug')->nullable();
            $table->decimal('cost', 8, 2)->nullable();
            $table->decimal('price', 8, 2);
            $table->decimal('previus_price', 8, 2)->nullable();
            $table->integer('stock')->nullable();
            $table->boolean('online')->default(1);
            $table->integer('user_id')->unsigned();
            $table->integer('brand_id')->unsigned()->nullable();
            $table->bigInteger('sub_category_id')->nullable()->unsigned();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->bigInteger('condition_id')->nullable()->unsigned();
            // $table->decimal('offer_price', 8, 2)->nullable();
            // $table->boolean('uncontable')->default(0);
            // $table->enum('measurement', ['gramo', 'kilo'])->nullable();
            $table->integer('featured')->nullable();

            $table->foreign('user_id')
                    ->references('id')->on('users');
            $table->foreign('sub_category_id')
                    ->references('id')->on('sub_categories');
                    
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
        Schema::dropIfExists('articles');
    }
}
