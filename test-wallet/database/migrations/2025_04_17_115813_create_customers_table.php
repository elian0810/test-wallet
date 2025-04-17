<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('document', 100)->comment('Hace referencia al documento de identidad.');  
            $table->string('name')->comment('Hace referencia al nombre del cliente.');  
            $table->string('email')->unique()->comment('Hace referencia al email del cliente.'); 
            $table->string('phone')->comment('Hace referencia al telenfono  del cliente');
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
        Schema::dropIfExists('customers');
    }
}
