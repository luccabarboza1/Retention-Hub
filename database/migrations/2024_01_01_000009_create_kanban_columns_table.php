<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kanban_columns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->unsignedSmallInteger('order')->default(0);
            $table->string('color', 30)->default('gray');
            $table->timestamps();
        });

        DB::table('kanban_columns')->insert([
            ['name' => 'Aberto',        'order' => 1, 'color' => 'blue',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Em Andamento',  'order' => 2, 'color' => 'yellow', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Retido',        'order' => 3, 'color' => 'green',  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Churn',         'order' => 4, 'color' => 'red',    'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('kanban_columns');
    }
};
