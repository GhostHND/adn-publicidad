<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | FLUJO DE PRODUCCIÓN DEL CATÁLOGO
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('catalog_production_steps')) {
            Schema::create('catalog_production_steps', function (Blueprint $table) {
                $table->id();

                $table->foreignId('catalog_item_id')
                    ->constrained('catalog_items')
                    ->cascadeOnDelete();

                $table->string('title', 200);

                $table->text('description')
                    ->nullable();

                $table->integer('sort_order')
                    ->default(0);

                $table->boolean('active')
                    ->default(true);

                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | RECETAS DE MATERIALES
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('catalog_material_recipes')) {
            Schema::create('catalog_material_recipes', function (Blueprint $table) {
                $table->id();

                $table->foreignId('catalog_item_id')
                    ->constrained('catalog_items')
                    ->cascadeOnDelete();

                $table->foreignId('inventory_item_id')
                    ->constrained('inventory_items')
                    ->restrictOnDelete();

                $table->string('calculation_method', 30)
                    ->default('UNIT');

                $table->decimal('quantity_rate', 14, 6)
                    ->default(1);

                $table->decimal('waste_percentage', 8, 2)
                    ->default(0);

                $table->text('notes')
                    ->nullable();

                $table->boolean('active')
                    ->default(true);

                $table->timestamps();

                $table->unique([
                    'catalog_item_id',
                    'inventory_item_id',
                ]);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | DATOS DE PRODUCCIÓN EN WORK ORDER ITEMS
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasTable('work_order_items')
            && !Schema::hasColumn('work_order_items', 'pricing_method')
        ) {
            Schema::table('work_order_items', function (Blueprint $table) {
                $table->string('pricing_method', 30)
                    ->nullable()
                    ->after('description');
            });
        }

        if (
            Schema::hasTable('work_order_items')
            && !Schema::hasColumn('work_order_items', 'measurement_unit')
        ) {
            Schema::table('work_order_items', function (Blueprint $table) {
                $table->string('measurement_unit', 50)
                    ->nullable()
                    ->after('pricing_method');
            });
        }

        if (
            Schema::hasTable('work_order_items')
            && !Schema::hasColumn('work_order_items', 'width')
        ) {
            Schema::table('work_order_items', function (Blueprint $table) {
                $table->decimal('width', 14, 4)
                    ->nullable()
                    ->after('measurement_unit');
            });
        }

        if (
            Schema::hasTable('work_order_items')
            && !Schema::hasColumn('work_order_items', 'height')
        ) {
            Schema::table('work_order_items', function (Blueprint $table) {
                $table->decimal('height', 14, 4)
                    ->nullable()
                    ->after('width');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | ORIGEN DE LOS MATERIALES DE LA OT
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasTable('work_order_materials')
            && !Schema::hasColumn('work_order_materials', 'source')
        ) {
            Schema::table('work_order_materials', function (Blueprint $table) {
                $table->string('source', 30)
                    ->default('manual')
                    ->after('inventory_item_id');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | RELACIONAR TAREA CON PRODUCTO DE LA OT
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasTable('tasks')
            && !Schema::hasColumn('tasks', 'work_order_item_id')
        ) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->foreignId('work_order_item_id')
                    ->nullable()
                    ->after('work_order_id')
                    ->constrained('work_order_items')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Como esta migración puede adoptar estructuras que ya existían,
        | el rollback debe ser conservador.
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasTable('tasks')
            && Schema::hasColumn('tasks', 'work_order_item_id')
        ) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropConstrainedForeignId('work_order_item_id');
            });
        }

        if (
            Schema::hasTable('work_order_materials')
            && Schema::hasColumn('work_order_materials', 'source')
        ) {
            Schema::table('work_order_materials', function (Blueprint $table) {
                $table->dropColumn('source');
            });
        }

        if (Schema::hasTable('work_order_items')) {
            $columns = [];

            foreach (
                [
                    'pricing_method',
                    'measurement_unit',
                    'width',
                    'height',
                ] as $column
            ) {
                if (
                    Schema::hasColumn(
                        'work_order_items',
                        $column
                    )
                ) {
                    $columns[] = $column;
                }
            }

            if (!empty($columns)) {
                Schema::table('work_order_items', function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }

        Schema::dropIfExists('catalog_material_recipes');
        Schema::dropIfExists('catalog_production_steps');
    }
};