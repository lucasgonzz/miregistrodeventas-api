<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            // $table->double('transaction_amount');
            $table->string('token');
            $table->string('description');
            $table->string('email');
            $table->integer('installments');
            $table->string('payment_method_id');
            $table->integer('issuer');
            $table->string('doc_type');
            $table->string('doc_number');
            $table->string('status')->nullable();
            $table->string('status_detail')->nullable();
            $table->integer('payment_id')->nullable();
            $table->integer('order_id')->unsigned()->nullable();
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
        Schema::dropIfExists('payments');
    }
}
