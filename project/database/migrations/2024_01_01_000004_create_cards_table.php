<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products');
            $table->string('status', 50)->default('Aberto');
            $table->dateTime('started_at');
            $table->dateTime('finished_at')->nullable();
            $table->string('ticket_origin', 100)->nullable();
            $table->string('ombudsman_agent', 100)->nullable();
            $table->string('ra_claim_link', 500)->nullable();
            $table->integer('rating')->nullable();
            $table->decimal('first_response_hours', 10, 2)->nullable();
            $table->decimal('ra_public_response_hours', 10, 2)->nullable();
            $table->decimal('usage_time_post_ombudsman_hours', 10, 2)->nullable();
            $table->string('contact_reason', 255)->nullable();
            $table->text('reason_details')->nullable();
            $table->string('responsible_team', 100)->nullable();
            $table->text('applied_solution')->nullable();
            $table->boolean('is_sector_recurrent')->default(false);
            $table->timestamps();
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->softDeletes();
            $table->string('deleted_by', 100)->nullable();

            $table->index('customer_id');
            $table->index('product_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
