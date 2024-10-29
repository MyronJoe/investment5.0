<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string("bit_address")->nullable();
            $table->string("bit_network")->nullable();
            $table->string("eth_address")->nullable();
            $table->string("eth_network")->nullable();
            $table->string("usd_address")->nullable();
            $table->string("usd_network")->nullable();
            $table->string("bank")->nullable();
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
        Schema::dropIfExists('addresses');
    }
};
