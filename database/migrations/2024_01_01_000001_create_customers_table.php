<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('company_name');
            $table->string('segment', 100)->nullable();
            $table->string('company_size', 50)->nullable();
            $table->integer('instagram_followers_count')->default(0);
            $table->string('email')->nullable();
            $table->decimal('monthly_fee', 10, 2)->nullable();
            $table->date('contracted_at')->nullable();
            $table->date('canceled_at')->nullable();
            $table->string('tier', 50)->nullable();
            $table->string('channel_type', 50)->nullable();
            $table->string('plan_name', 100)->nullable();
            $table->boolean('has_chatbot')->default(false);
            $table->boolean('has_ai')->default(false);
            $table->boolean('has_implementation')->default(false);
            $table->timestamps();
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->softDeletes();
            $table->string('deleted_by', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
