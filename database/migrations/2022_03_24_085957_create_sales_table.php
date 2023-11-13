<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('receipt_no');
            $table->unsignedBigInteger('product_id');
            $table->float('price');
            $table->float('quantity');
            $table->integer('discount')->default(0);
            $table->string('payment_method');
            $table->integer('payment_amount')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('note')->nullable();
            $table->string('status')->nullable();
            $table->boolean('collected')->default(1);
            $table->integer('returned_qty')->default(0);
            $table->timestamps();
        
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('set null');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales');
    }
}
