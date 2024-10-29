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
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->string('investor')->nullable();
            $table->string('planName')->nullable();
            $table->string('min')->nullable();
            $table->string('max')->nullable();
            $table->string('RIO')->nullable();
            $table->string('duration')->nullable();
            $table->string('status')->nullable();
            $table->string('method')->nullable();
            $table->string('amount')->nullable();
            $table->text('name')->nullable();
            $table->string('prove-img')->nullable();
            $table->string('token')->nullable();
            $table->string('daily_percent')->nullable();
            $table->string('total')->nullable();
            $table->string('profit')->nullable();
            $table->string('daily_income')->nullable();
            $table->string('day_num')->nullable();
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
        Schema::dropIfExists('investments');
    }
};
