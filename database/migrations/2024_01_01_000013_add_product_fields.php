<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('plan_name', 100)->nullable()->after('product_type');
            $table->integer('attendants_count')->nullable()->after('plan_name');
            $table->json('host_services')->nullable()->after('attendants_count');
        });

        Schema::create('product_plan_configs', function (Blueprint $table) {
            $table->id();
            $table->string('product_type', 20);
            $table->string('plan_name', 100);
            $table->decimal('price_per_unit', 10, 2)->default(0);
            $table->string('unit_label', 50)->default('unidade');
            $table->timestamps();
        });

        // Seed padrão para Talk2
        DB::table('product_plan_configs')->insert([
            ['product_type' => 'Talk2', 'plan_name' => 'Professional', 'price_per_unit' => 0.00, 'unit_label' => 'por atendente', 'created_at' => now(), 'updated_at' => now()],
            ['product_type' => 'Talk2', 'plan_name' => 'Enterprise',   'price_per_unit' => 0.00, 'unit_label' => 'por atendente', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('product_plan_configs');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['plan_name', 'attendants_count', 'host_services']);
        });
    }
};
