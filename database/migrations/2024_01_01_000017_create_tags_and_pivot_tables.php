<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Criar tabela de tags
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // 'customer' ou 'card'
            $table->timestamps();

            $table->unique(['name', 'type']);
        });

        // 2. Criar tabelas pivô
        Schema::create('customer_tag', function (Blueprint $table) {
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->primary(['customer_id', 'tag_id']);
        });

        Schema::create('card_tag', function (Blueprint $table) {
            $table->foreignId('card_id')->constrained('cards')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->primary(['card_id', 'tag_id']);
        });

        // Helper para buscar ou criar tag
        $getOrCreateTag = function ($name, $type) {
            $tag = DB::table('tags')->where('name', $name)->where('type', $type)->first();
            if ($tag) {
                return $tag->id;
            }
            return DB::table('tags')->insertGetId([
                'name' => $name,
                'type' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        };

        // 3. Migrar dados de tags de clientes
        $customers = DB::table('customers')->whereNotNull('tags')->get();
        foreach ($customers as $c) {
            $tags = json_decode($c->tags, true);
            if (is_array($tags)) {
                foreach ($tags as $tagName) {
                    $tagName = trim($tagName);
                    if ($tagName === '') continue;
                    $tagId = $getOrCreateTag($tagName, 'customer');
                    
                    DB::table('customer_tag')->insertOrIgnore([
                        'customer_id' => $c->id,
                        'tag_id' => $tagId,
                    ]);
                }
            }
        }

        // 4. Migrar dados de tags de cards
        $cards = DB::table('cards')->whereNotNull('tags')->get();
        foreach ($cards as $card) {
            $tags = json_decode($card->tags, true);
            if (is_array($tags)) {
                foreach ($tags as $tagName) {
                    $tagName = trim($tagName);
                    if ($tagName === '') continue;
                    $tagId = $getOrCreateTag($tagName, 'card');
                    
                    DB::table('card_tag')->insertOrIgnore([
                        'card_id' => $card->id,
                        'tag_id' => $tagId,
                    ]);
                }
            }
        }

        // 5. Remover colunas JSON antigas
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('tags');
        });

        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->json('tags')->nullable()->after('related_emails');
        });

        Schema::table('cards', function (Blueprint $table) {
            $table->json('tags')->nullable()->after('is_sector_recurrent');
        });

        // Re-popular colunas JSON a partir das tabelas pivô
        $customers = DB::table('customers')->get();
        foreach ($customers as $c) {
            $tagNames = DB::table('customer_tag')
                ->join('tags', 'customer_tag.tag_id', '=', 'tags.id')
                ->where('customer_tag.customer_id', $c->id)
                ->pluck('tags.name')
                ->toArray();
            
            if (count($tagNames)) {
                DB::table('customers')->where('id', $c->id)->update([
                    'tags' => json_encode($tagNames)
                ]);
            }
        }

        $cards = DB::table('cards')->get();
        foreach ($cards as $card) {
            $tagNames = DB::table('card_tag')
                ->join('tags', 'card_tag.tag_id', '=', 'tags.id')
                ->where('card_tag.card_id', $card->id)
                ->pluck('tags.name')
                ->toArray();
            
            if (count($tagNames)) {
                DB::table('cards')->where('id', $card->id)->update([
                    'tags' => json_encode($tagNames)
                ]);
            }
        }

        Schema::dropIfExists('customer_tag');
        Schema::dropIfExists('card_tag');
        Schema::dropIfExists('tags');
    }
};
