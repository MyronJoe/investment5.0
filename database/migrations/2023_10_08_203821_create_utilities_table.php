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
        Schema::create('utilities', function (Blueprint $table) {
            $table->id();
            $table->text('wallet_address')->nullable();
            $table->text('site_link')->nullable();
            $table->text('email')->nullable();
            $table->text('phone')->nullable();
            $table->text('description')->nullable();
            $table->text('site_name')->nullable();
            $table->text('whitelogo')->nullable();
            $table->text('darkLogo')->nullable();
            $table->text('faveicon')->nullable();
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
        Schema::dropIfExists('utilities');
    }
};
