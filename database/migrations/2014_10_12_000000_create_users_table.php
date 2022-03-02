<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 128)->nullable();
            $table->string('city', 128)->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable()->unique();
            $table->string('company_name', 128)->nullable();
            $table->enum('type', ['commerce', 'provider'])->default('commerce');
            $table->string('password', 128);
            $table->integer('owner_id')->nullable()->unsigned();
            $table->integer('plan_id')->unsigned()->nullable();
            // $table->integer('admin_id')->nullable()->unsigned();
            $table->integer('percentage_card')->nullable();
            $table->string('iva')->nullable();
            $table->string('dni')->nullable();
            $table->string('cuit')->nullable();
            $table->boolean('has_delivery')->default(1);
            $table->decimal('delivery_price')->nullable();
            $table->enum('online_prices', ['all', 'only_registered'])->nullable();
            $table->string('order_description')->nullable();
            $table->boolean('with_dolar')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->string('online')->nullable();
            $table->rememberToken();

            $table->enum('status', ['commerce', 'admin', 'super']);

            // $table->foreign('owner_id')->references('id')->on('users');
            // $table->foreign('admin_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
