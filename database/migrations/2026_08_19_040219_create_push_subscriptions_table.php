<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->char('endpoint_hash', 64)
                ->unique();

            $table->text('endpoint');

            $table->text('p256dh');

            $table->text('auth');

            $table->string('content_encoding', 50)
                ->default('aes128gcm');

            $table->text('user_agent')
                ->nullable();

            $table->timestamp('last_used_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'user_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'push_subscriptions'
        );
    }
};