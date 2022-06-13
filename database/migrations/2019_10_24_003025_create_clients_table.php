<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name', 128);
            $table->string('surname', 128)->nullable();
            $table->string('email', 128)->nullable();
            $table->string('address', 128)->nullable();
            $table->string('cuit', 128)->nullable();
            $table->string('razon_social', 128)->nullable();
            $table->integer('iva_condition_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned();
            $table->bigInteger('seller_id')->nullable()->unsigned();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->foreign('user_id')
                    ->references('id')->on('users');
            $table->foreign('seller_id')
                    ->references('id')->on('sellers');

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
        Schema::dropIfExists('clients');
    }
}
