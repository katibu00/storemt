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
        Schema::create('estimates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('receipt_no');
            $table->unsignedBigInteger('product_id');
            $table->integer('price');
            $table->float('quantity');
            $table->integer('discount')->default(0);
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('customer_id');
            $table->string('note')->nullable();
            $table->timestamps();
        
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('users')->onDelete('set null');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estimates');
    }
};
