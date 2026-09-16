<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();

            $table->string('income_number', 30)
                ->unique();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('cash_movement_id')
                ->nullable()
                ->constrained('cash_movements')
                ->nullOnDelete();

            $table->date('income_date');

            $table->string('category', 100);

            $table->string('description', 200);

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

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->string('expense_number', 30)
                ->unique();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('cash_movement_id')
                ->nullable()
                ->constrained('cash_movements')
                ->nullOnDelete();

            $table->date('expense_date');

            $table->string('category', 100);

            $table->string('description', 200);

            $table->decimal('amount', 12, 2);

            $table->string('payment_method', 30)
                ->default('cash');

            $table->string('reference', 150)
                ->nullable();

            $table->string('supplier', 150)
                ->nullable();

            $table->text('notes')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('incomes');
    }
};