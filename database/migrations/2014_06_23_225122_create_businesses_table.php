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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username');
            $table->string('logo')->nullable();
            $table->tinyInteger('has_branches')->default(0);
            $table->unsignedBigInteger('registered_by')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->timestamps();
        
            // $table->foreign('registered_by')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('businesses');
    }
};
