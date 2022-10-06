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
            $table->integer('num')->nullable();
            $table->string('bar_code', 128)->nullable();
            $table->string('provider_code', 128)->nullable();
            $table->text('name')->nullable();
            $table->string('slug')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('percentage_gain', 8, 2)->nullable();
            $table->decimal('previus_price', 12, 2)->nullable();
            $table->integer('stock')->nullable();
            $table->integer('stock_min')->nullable();
            $table->boolean('online')->default(1);
            // $table->boolean('with_dolar')->default(0);
            $table->integer('user_id')->unsigned();
            $table->integer('brand_id')->unsigned()->nullable();
            $table->integer('iva_id')->unsigned()->default(2)->nullable();
            $table->bigInteger('sub_category_id')->nullable()->unsigned();
            $table->enum('status', ['active', 'inactive', 'from_provider_order', 'from_budget'])->default('active');
            $table->bigInteger('condition_id')->nullable()->unsigned();
            $table->integer('featured')->nullable();
            $table->boolean('cost_in_dollars')->default(0)->nullable();
            $table->boolean('apply_provider_percentage_gain')->default(1)->nullable();

            $table->foreign('user_id')
                    ->references('id')->on('users');
            // $table->foreign('sub_category_id')
            //         ->references('id')->on('sub_categories');
                    
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
