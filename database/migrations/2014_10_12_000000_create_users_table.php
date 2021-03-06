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
            $table->bigIncrements('id');
            $table->string('name', 128)->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('company_name', 128)->nullable();
            $table->string('password', 128);
            $table->integer('owner_id')->nullable()->unsigned();
            // $table->integer('admin_id')->nullable()->unsigned();
            $table->decimal('percentage_card')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('expire')->nullable();
            $table->string('online')->nullable();
            $table->rememberToken();

            $table->enum('status', ['for_trial', 'trial', 'in_use', 'admin', 'super', 'recommended']);

            $table->foreign('owner_id')->references('id')->on('users');
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
