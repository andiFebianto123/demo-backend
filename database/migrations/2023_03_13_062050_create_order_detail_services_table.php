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
        Schema::create('order_detail_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_detail_id');
            $table->unsignedBigInteger('mitra_id');
            $table->unsignedBigInteger('message_service_id');
            $table->timestamps();

            $table->foreign('order_detail_id')
            ->references('id')
            ->on('order_details')
            ->onUpdate('cascade');

            $table->foreign('mitra_id')
            ->references('id')
            ->on('mitras')
            ->onUpdate('cascade');

            $table->foreign('message_service_id')
            ->references('id')
            ->on('message_services')
            ->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_detail_services');
    }
};
