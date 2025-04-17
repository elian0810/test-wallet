<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->comment('ID del cliente al cual pertenece la línea de crédito');
            $table->double('balance', 20, 3)->comment('Saldo actual de la línea de crédito.');
            $table->double('total_debt', 20, 3)->default(0)->comment('Especifica el total de la deuda de la línea de crédito.');
            $table->double('total_consumption', 20, 3)->default(0)->comment('Especifica el consumo total que lleva una linea de credito en pesos.');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')
            ->onUpdate('cascade')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_lines');
    }
}
