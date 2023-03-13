<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('mitra_id');
            $table->enum('status', ['pending', 'rejected', 'approved']);
            $table->timestamps();

            $table->foreign('order_id')
            ->references('id')
            ->on('orders')
            ->onUpdate('cascade');

            $table->foreign('mitra_id')
            ->references('id')
            ->on('mitras')
            ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
