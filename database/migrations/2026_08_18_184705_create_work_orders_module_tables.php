<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();

            $table->string('work_order_number', 30)
                ->unique();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->restrictOnDelete();

            $table->foreignId('sale_id')
                ->nullable()
                ->constrained('sales')
                ->nullOnDelete();

            $table->foreignId('responsible_employee_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title', 200);

            $table->string('status', 30)
                ->default('pending');

            $table->string('priority', 20)
                ->default('normal');

            $table->date('order_date');

            $table->date('due_date')
                ->nullable();

            $table->dateTime('started_at')
                ->nullable();

            $table->dateTime('completed_at')
                ->nullable();

            $table->dateTime('delivered_at')
                ->nullable();

            $table->text('description')
                ->nullable();

            $table->text('internal_notes')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('work_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_order_id')
                ->constrained('work_orders')
                ->cascadeOnDelete();

            $table->foreignId('catalog_item_id')
                ->nullable()
                ->constrained('catalog_items')
                ->nullOnDelete();

            $table->string('item_code', 50)
                ->nullable();

            $table->string('item_name', 200);

            $table->text('description')
                ->nullable();

            $table->decimal('quantity', 12, 3)
                ->default(1);

            $table->decimal('unit_price', 14, 2)
                ->default(0);

            $table->decimal('subtotal', 14, 2)
                ->default(0);

            $table->integer('sort_order')
                ->default(0);

            $table->timestamps();
        });

        Schema::create('work_order_materials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_order_id')
                ->constrained('work_orders')
                ->cascadeOnDelete();

            $table->foreignId('inventory_item_id')
                ->constrained('inventory_items')
                ->restrictOnDelete();

            $table->decimal('quantity_planned', 14, 3);

            $table->decimal('quantity_consumed', 14, 3)
                ->default(0);

            $table->decimal('unit_cost_snapshot', 14, 4)
                ->default(0);

            $table->dateTime('consumed_at')
                ->nullable();

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'work_order_id',
                'inventory_item_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_materials');
        Schema::dropIfExists('work_order_items');
        Schema::dropIfExists('work_orders');
    }
};