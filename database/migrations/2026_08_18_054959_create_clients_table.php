<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->string('client_code', 20)->unique();

            $table->string('client_type', 30)
                ->default('natural');

            $table->string('first_name', 100)->nullable();
            $table->string('middle_name', 100)->nullable();

            $table->string('last_name', 100)->nullable();
            $table->string('second_last_name', 100)->nullable();

            $table->string('business_name', 180)->nullable();

            $table->string('identity_number', 30)
                ->nullable()
                ->unique();

            $table->string('rtn', 30)
                ->nullable()
                ->unique();

            $table->string('contact_person', 150)->nullable();

            $table->string('email', 150)->nullable();

            $table->string('phone', 30)->nullable();
            $table->string('alternate_phone', 30)->nullable();

            $table->text('address')->nullable();

            $table->string('city', 100)->nullable();

            $table->text('notes')->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};