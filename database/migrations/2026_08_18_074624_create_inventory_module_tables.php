<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();

            $table->string('item_code', 30)
                ->unique();

            $table->string('name', 150);

            $table->string('category', 100)
                ->nullable();

            $table->string('measurement_unit', 50);

            $table->decimal('current_stock', 14, 3)
                ->default(0);

            $table->decimal('minimum_stock', 14, 3)
                ->default(0);

            $table->decimal('unit_cost', 14, 4)
                ->default(0);

            $table->string('supplier', 150)
                ->nullable();

            $table->string('location', 150)
                ->nullable();

            $table->boolean('active')
                ->default(true);

            $table->text('notes')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_item_id')
                ->constrained('inventory_items')
                ->restrictOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('movement_type', 30);

            $table->decimal('quantity', 14, 3);

            $table->decimal('previous_stock', 14, 3);

            $table->decimal('resulting_stock', 14, 3);

            $table->decimal('unit_cost', 14, 4)
                ->nullable();

            $table->string('reference', 150)
                ->nullable();

            $table->text('notes')
                ->nullable();

            $table->dateTime('moved_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_items');
    }
};