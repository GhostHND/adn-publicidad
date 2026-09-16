<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quotation_id')
                ->constrained('quotations')
                ->cascadeOnDelete();

            $table->foreignId('catalog_item_id')
                ->nullable()
                ->constrained('catalog_items')
                ->nullOnDelete();

            $table->string('item_code', 30)->nullable();

            $table->string('item_name', 180);

            $table->text('description')->nullable();

            $table->string('pricing_method', 30);

            $table->string('measurement_unit', 30)->nullable();

            $table->decimal('quantity', 12, 2)
                ->default(1);

            $table->decimal('width', 12, 4)
                ->nullable();

            $table->decimal('height', 12, 4)
                ->nullable();

            $table->decimal('unit_price', 12, 4)
                ->nullable();

            $table->decimal('unit_cost', 12, 4)
                ->nullable();

            $table->decimal('sale_rate', 12, 4)
                ->nullable();

            $table->decimal('cost_rate', 12, 4)
                ->nullable();

            $table->decimal('subtotal', 12, 2);

            $table->decimal('estimated_cost', 12, 2)
                ->default(0);

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};