<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('opened_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('closed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->dateTime('opened_at');

            $table->dateTime('closed_at')
                ->nullable();

            $table->string('status', 20)
                ->default('open');

            $table->decimal('opening_balance', 12, 2)
                ->default(0);

            $table->decimal('expected_balance', 12, 2)
                ->nullable();

            $table->decimal('closing_balance', 12, 2)
                ->nullable();

            $table->decimal('difference', 12, 2)
                ->nullable();

            $table->text('opening_notes')
                ->nullable();

            $table->text('closing_notes')
                ->nullable();

            $table->timestamps();
        });

        Schema::table('sale_payments', function (Blueprint $table) {
            $table->foreignId('cash_session_id')
                ->nullable()
                ->after('sale_id')
                ->constrained('cash_sessions')
                ->nullOnDelete();
        });

        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cash_session_id')
                ->constrained('cash_sessions')
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('movement_type', 30);

            $table->decimal('amount', 12, 2);

            $table->string('description', 200);

            $table->string('reference', 150)
                ->nullable();

            $table->dateTime('moved_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');

        Schema::table('sale_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId(
                'cash_session_id'
            );
        });

        Schema::dropIfExists('cash_sessions');
    }
};