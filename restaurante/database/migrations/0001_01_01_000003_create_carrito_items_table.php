<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrito_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plato_id')->constrained('platos')->onDelete('cascade');
            $table->unsignedInteger('cantidad')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'plato_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrito_items');
    }
};
