<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->string('sale_number', 30)->unique();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->restrictOnDelete();

            $table->foreignId('quotation_id')
                ->nullable()
                ->unique()
                ->constrained('quotations')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('sale_date');

            $table->string('status', 30)
                ->default('pending');

            $table->decimal('subtotal', 12, 2)
                ->default(0);

            $table->decimal('discount', 12, 2)
                ->default(0);

            $table->decimal('total', 12, 2)
                ->default(0);

            $table->decimal('estimated_cost', 12, 2)
                ->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                ->constrained('sales')
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

        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();

            $table->string('receipt_number', 30)
                ->unique();

            $table->foreignId('sale_id')
                ->constrained('sales')
                ->cascadeOnDelete();

            $table->foreignId('received_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('payment_date');

            $table->decimal('amount', 12, 2);

            $table->string('payment_method', 30)
                ->default('cash');

            $table->string('reference', 150)
                ->nullable();

            $table->text('notes')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};