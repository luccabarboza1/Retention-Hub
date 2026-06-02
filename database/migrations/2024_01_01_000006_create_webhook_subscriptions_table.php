<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('url', 2048);
            $table->enum('trigger_type', ['card.created', 'card.updated', 'card.finished', 'customer.updated']);
            $table->text('secret');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->softDeletes();
            $table->string('deleted_by', 100)->nullable();

            $table->index(['trigger_type', 'is_active', 'deleted_at'], 'idx_trigger_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_subscriptions');
    }
};
