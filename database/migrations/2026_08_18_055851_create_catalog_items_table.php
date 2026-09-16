<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_items', function (Blueprint $table) {
            $table->id();

            $table->string('item_code', 20)->unique();

            $table->string('name', 150);

            $table->text('description')->nullable();

            $table->string('item_type', 30)
                ->default('service');

            $table->string('category', 100)
                ->nullable();

            $table->string('pricing_method', 30)
                ->default('FIXED');

            $table->string('measurement_unit', 30)
                ->nullable();

            $table->decimal('cost_price', 12, 2)
                ->nullable();

            $table->decimal('sale_price', 12, 2)
                ->nullable();

            $table->decimal('cost_rate', 12, 4)
                ->nullable();

            $table->decimal('sale_rate', 12, 4)
                ->nullable();

            $table->decimal('margin_percentage', 8, 2)
                ->nullable();

            $table->boolean('active')
                ->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_items');
    }
};