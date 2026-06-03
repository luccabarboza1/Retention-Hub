<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers');
            $table->string('external_id');
            $table->string('contract_identifier')->nullable();
            $table->enum('product_type', ['Host', 'Talk2']);
            $table->decimal('consumption', 10, 2)->default(0.00);
            $table->enum('status', ['ativo', 'cancelado'])->default('ativo');
            $table->dateTime('external_created_at')->nullable();
            $table->timestamps();
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->softDeletes();
            $table->string('deleted_by', 100)->nullable();

            $table->unique(['external_id', 'product_type'], 'uk_external_product');
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
