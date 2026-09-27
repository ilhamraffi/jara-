<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('list_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('lists')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['owner', 'member'])->default('member');
            $table->timestamps();

            $table->unique(['list_id', 'user_id'], 'unique_member');
            $table->index('list_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('list_members');
    }
};
