<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleProviderOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_provider_order', function (Blueprint $table) {
            $table->id();
            $table->integer('article_id')->unsigned();
            $table->integer('provider_order_id')->unsigned();
            $table->integer('amount');
            $table->integer('received')->default(0);
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('article_provider_order');
    }
}
