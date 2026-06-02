<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adiciona nova coluna JSON
        Schema::table('webhook_subscriptions', function (Blueprint $table) {
            $table->json('trigger_types')->nullable()->after('trigger_type');
        });

        // Migra dados existentes: wrap do valor atual em array JSON
        DB::statement('UPDATE webhook_subscriptions SET trigger_types = JSON_ARRAY(trigger_type) WHERE trigger_type IS NOT NULL');

        // Remove índice antigo e coluna antiga
        Schema::table('webhook_subscriptions', function (Blueprint $table) {
            $table->dropIndex('idx_trigger_active');
            $table->dropColumn('trigger_type');
        });

        // Torna não-nullable e recria índice útil
        Schema::table('webhook_subscriptions', function (Blueprint $table) {
            $table->json('trigger_types')->nullable(false)->change();
            $table->index(['is_active', 'deleted_at'], 'idx_active');
        });
    }

    public function down(): void
    {
        Schema::table('webhook_subscriptions', function (Blueprint $table) {
            $table->enum('trigger_type', ['card.created', 'card.updated', 'card.finished', 'customer.updated'])
                  ->nullable()->after('url');
        });

        DB::statement("UPDATE webhook_subscriptions SET trigger_type = JSON_UNQUOTE(JSON_EXTRACT(trigger_types, '$[0]'))");

        Schema::table('webhook_subscriptions', function (Blueprint $table) {
            $table->dropIndex('idx_active');
            $table->dropColumn('trigger_types');
            $table->string('trigger_type', 50)->nullable(false)->change();
            $table->index(['trigger_type', 'is_active', 'deleted_at'], 'idx_trigger_active');
        });
    }
};
