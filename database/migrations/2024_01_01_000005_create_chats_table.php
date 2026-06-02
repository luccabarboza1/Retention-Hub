<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('ombudsman_card_id')->constrained('cards')->cascadeOnDelete();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->softDeletes();
            $table->string('deleted_by', 100)->nullable();

            $table->index('ombudsman_card_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
