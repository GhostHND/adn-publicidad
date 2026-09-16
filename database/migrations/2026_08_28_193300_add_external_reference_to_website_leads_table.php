<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasColumn(
                'website_leads',
                'external_reference'
            )
        ) {
            return;
        }

        Schema::table(
            'website_leads',
            function (
                Blueprint $table
            ): void {
                $table
                    ->string(
                        'external_reference',
                        80
                    )
                    ->nullable()
                    ->unique()
                    ->after(
                        'lead_number'
                    );
            }
        );
    }

    public function down(): void
    {
        if (
            !Schema::hasColumn(
                'website_leads',
                'external_reference'
            )
        ) {
            return;
        }

        Schema::table(
            'website_leads',
            function (
                Blueprint $table
            ): void {
                $table->dropUnique([
                    'external_reference',
                ]);

                $table->dropColumn(
                    'external_reference'
                );
            }
        );
    }
};