<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // Critical: Isolation by branch
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            
            $table->string('barcode')->index(); // Scan speed is priority
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->decimal('cost', 10, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock')->default(5);
            
            // For on-the-fly concepts (Venta Rápida)
            $table->boolean('is_manual_concept')->default(false);
            
            $table->timestamps();
            $table->softDeletes();

            // Unique constraint: A barcode must be unique ONLY within a specific branch.
            // Branch A can have Barcode 123, Branch B can also have Barcode 123.
            $table->unique(['branch_id', 'barcode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
