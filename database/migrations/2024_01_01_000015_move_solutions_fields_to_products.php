<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        // 1. Add columns to products table
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_chatbot')->default(false)->after('status');
            $table->boolean('has_ai')->default(false)->after('has_chatbot');
            $table->boolean('has_implementation')->default(false)->after('has_ai');
        });
 
        // 2. Migrate existing data from customers to products
        $customers = \Illuminate\Support\Facades\DB::table('customers')->get();
        foreach ($customers as $customer) {
            \Illuminate\Support\Facades\DB::table('products')
                ->where('customer_id', $customer->id)
                ->where('product_type', 'Talk2')
                ->update([
                    'has_chatbot' => $customer->has_chatbot ?? false,
                    'has_ai' => $customer->has_ai ?? false,
                    'has_implementation' => $customer->has_implementation ?? false,
                ]);
        }
 
        // 3. Drop columns from customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['has_chatbot', 'has_ai', 'has_implementation']);
        });
    }
 
    public function down(): void
    {
        // 1. Add columns back to customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('has_chatbot')->default(false);
            $table->boolean('has_ai')->default(false);
            $table->boolean('has_implementation')->default(false);
        });
 
        // 2. Migrate data back from products to customers
        $products = \Illuminate\Support\Facades\DB::table('products')
            ->where('product_type', 'Talk2')
            ->get();
        foreach ($products as $product) {
            \Illuminate\Support\Facades\DB::table('customers')
                ->where('id', $product->customer_id)
                ->update([
                    'has_chatbot' => $product->has_chatbot,
                    'has_ai' => $product->has_ai,
                    'has_implementation' => $product->has_implementation,
                ]);
        }
 
        // 3. Drop columns from products table
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['has_chatbot', 'has_ai', 'has_implementation']);
        });
    }
};
