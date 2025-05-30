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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('importer');
            $table->unsignedBigInteger('shipmentProductsCount')->default(0);
            $table->decimal('totalPrice', 15, 2)->default(0);
            $table->decimal('paidAmount', 10, 2);
            $table->decimal('remainingAmount', 10, 2)->nullable();
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->timestamp('creationDate')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
