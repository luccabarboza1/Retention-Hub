<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_dispatch_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->enum('event_type', ['card.created', 'card.updated', 'card.finished', 'customer.updated']);
            $table->unsignedBigInteger('event_entity_id');
            $table->unsignedTinyInteger('attempt_number')->default(1);
            $table->unsignedTinyInteger('max_attempts')->default(5);
            $table->enum('status', ['pending', 'success', 'failed', 'permanently_failed', 'abandoned'])->default('pending');
            $table->json('payload');
            $table->string('target_url', 2048);
            $table->smallInteger('http_status')->nullable();
            $table->text('response_body')->nullable();
            $table->string('error_message', 1000)->nullable();
            $table->dateTime('dispatched_at')->nullable();
            $table->dateTime('responded_at')->nullable();
            $table->dateTime('next_retry_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['subscription_id', 'status'], 'idx_subscription_status');
            $table->index(['status', 'next_retry_at'], 'idx_status_next_retry');
            $table->index(['event_type', 'event_entity_id'], 'idx_event_entity');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_dispatch_logs');
    }
};
