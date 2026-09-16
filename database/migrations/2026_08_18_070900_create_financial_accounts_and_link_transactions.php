<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->id();

            $table->string('account_code', 20)
                ->unique();

            $table->string('name', 150);

            $table->string('institution', 150)
                ->nullable();

            $table->string('account_type', 30)
                ->default('bank_account');

            $table->string('currency', 10)
                ->default('HNL');

            $table->decimal('opening_balance', 14, 2)
                ->default(0);

            $table->boolean('active')
                ->default(true);

            $table->text('notes')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('sale_payments', function (Blueprint $table) {
            $table->foreignId('financial_account_id')
                ->nullable()
                ->after('cash_session_id')
                ->constrained('financial_accounts')
                ->nullOnDelete();
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->foreignId('financial_account_id')
                ->nullable()
                ->after('cash_movement_id')
                ->constrained('financial_accounts')
                ->nullOnDelete();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('financial_account_id')
                ->nullable()
                ->after('cash_movement_id')
                ->constrained('financial_accounts')
                ->nullOnDelete();
        });

        $now = now();

        DB::table('financial_accounts')->insert([
            [
                'account_code' => 'CTA-0001',
                'name' => 'BAC',
                'institution' => 'BAC',
                'account_type' => 'bank_account',
                'currency' => 'HNL',
                'opening_balance' => 0,
                'active' => true,
                'notes' => 'Cuenta bancaria principal BAC.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'account_code' => 'CTA-0002',
                'name' => 'Banco Atlántida',
                'institution' => 'Banco Atlántida',
                'account_type' => 'bank_account',
                'currency' => 'HNL',
                'opening_balance' => 0,
                'active' => true,
                'notes' => 'Cuenta bancaria Banco Atlántida.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'account_code' => 'CTA-0003',
                'name' => 'Banpaís',
                'institution' => 'Banpaís',
                'account_type' => 'bank_account',
                'currency' => 'HNL',
                'opening_balance' => 0,
                'active' => true,
                'notes' => 'Cuenta bancaria Banpaís.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId(
                'financial_account_id'
            );
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->dropConstrainedForeignId(
                'financial_account_id'
            );
        });

        Schema::table('sale_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId(
                'financial_account_id'
            );
        });

        Schema::dropIfExists('financial_accounts');
    }
};