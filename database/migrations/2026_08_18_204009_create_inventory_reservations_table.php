<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventory_reservations')) {
            Schema::create('inventory_reservations', function (Blueprint $table) {
                $table->id();

                $table->foreignId('work_order_id')
                    ->constrained('work_orders')
                    ->cascadeOnDelete();

                $table->foreignId('work_order_material_id')
                    ->unique()
                    ->constrained('work_order_materials')
                    ->cascadeOnDelete();

                $table->foreignId('inventory_item_id')
                    ->constrained('inventory_items')
                    ->restrictOnDelete();

                $table->decimal('quantity_required', 14, 3)
                    ->default(0);

                $table->decimal('quantity_reserved', 14, 3)
                    ->default(0);

                $table->decimal('quantity_consumed', 14, 3)
                    ->default(0);

                /*
                |--------------------------------------------------------------------------
                | Estados
                |--------------------------------------------------------------------------
                |
                | reserved    = reserva completa
                | partial     = reserva parcial
                | unavailable = no había existencia disponible
                | consumed    = ya se descontó del inventario
                | released    = reserva liberada
                |
                */

                $table->string('status', 30)
                    ->default('unavailable');

                $table->dateTime('reserved_at')
                    ->nullable();

                $table->dateTime('consumed_at')
                    ->nullable();

                $table->dateTime('released_at')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'inventory_item_id',
                    'status',
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_reservations');
    }
};