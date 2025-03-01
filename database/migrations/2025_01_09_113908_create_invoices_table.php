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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('customerName');
            $table->string('sellerName');
            $table->timestamp('creationDate')->nullable();
            $table->unsignedBigInteger('invoiceProductCount')->default(0);
            $table->decimal('totalInvoicePrice', 15, 2)->default(0);
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('extraAmount', 10, 2)->nullable();
            $table->decimal('invoiceAfterDiscount', 15, 2)->nullable();
            $table->decimal('profit', 10, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
