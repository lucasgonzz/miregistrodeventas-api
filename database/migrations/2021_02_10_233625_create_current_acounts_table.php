<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrentAcountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('current_acounts', function (Blueprint $table) {
            $table->id();
            $table->string('detalle')->nullable();
            $table->integer('page')->nullable();
            $table->string('description')->nullable();
            $table->decimal('debe', 12,2)->nullable();
            $table->decimal('haber', 12,2)->nullable();
            $table->decimal('saldo', 12,2)->nullable();
            $table->enum('status', [
                'saldo_inicial',
                'sin_pagar', 
                'pagandose', 
                'pagado', 
                'nota_credito',
                'pago_from_client',
                'pago_for_commissioner',
            ]);
            $table->decimal('pagandose', 8,2)->nullable();
            $table->bigInteger('client_id')->unsigned()->nullable();
            $table->foreign('client_id')->references('id')->on('clients');
            $table->bigInteger('commissioner_id')->unsigned()->nullable();
            $table->foreign('commissioner_id')->references('id')->on('commissioners');
            $table->bigInteger('seller_id')->unsigned()->nullable();
            $table->foreign('seller_id')->references('id')->on('sellers');
            $table->bigInteger('sale_id')->unsigned()->nullable();
            $table->foreign('sale_id')->references('id')->on('sales');
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
        Schema::dropIfExists('current_acounts');
    }
}
