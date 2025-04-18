<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('credit_line_id')->nullable()->comment('ID de la línea de crédito');
            $table->string('token')->nullable()->comment('Hace referencia al token.');  
            $table->double('value', 20, 3)->default(0)->comment('Especifica el valor a pagar.');
            $table->string('timeout_token')->nullable()->comment('Hace referencia al token de tiempo de espera.');  
            $table->string('uuid')->nullable()->comment('Hace referencia al uuid del token.');  
            $table->timestamps();

            $table->foreign('credit_line_id')->references('id')->on('credit_lines')
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
        Schema::dropIfExists('tokens');
    }
}
