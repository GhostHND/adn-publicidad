<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_leads', function (Blueprint $table) {
            $table->foreignId('client_id')
                ->nullable()
                ->after('assigned_employee_id')
                ->constrained('clients')
                ->nullOnDelete();

            $table->foreignId('quotation_id')
                ->nullable()
                ->after('client_id')
                ->constrained('quotations')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')
                ->nullable()
                ->after('contacted_at');

            $table->timestamp('attended_at')
                ->nullable()
                ->after('reviewed_at');

            $table->index([
                'status',
                'reviewed_at',
            ]);

            $table->index([
                'client_id',
                'quotation_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('website_leads', function (Blueprint $table) {
            $table->dropIndex([
                'status',
                'reviewed_at',
            ]);

            $table->dropIndex([
                'client_id',
                'quotation_id',
            ]);

            $table->dropConstrainedForeignId(
                'quotation_id'
            );

            $table->dropConstrainedForeignId(
                'client_id'
            );

            $table->dropColumn([
                'reviewed_at',
                'attended_at',
            ]);
        });
    }
};