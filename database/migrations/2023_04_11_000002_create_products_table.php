<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->double('price')->default(0);
            $table->double('old_price')->default(0);
            $table->string('image')->nullable();
            $table->integer('pro_count')->unsigned()->default(1);
            $table->double('discount')->default(0);
            $table->text('description');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }


    public function down()
    {
        Schema::dropIfExists('products');
    }
};
