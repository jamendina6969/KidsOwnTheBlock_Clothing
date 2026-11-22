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
            $table->string('slug')->unique();
            $table->string('title');
            $table->decimal('price', 8, 2);
            $table->string('category');
            $table->json('bands');
            $table->boolean('sold_out')->default(false);
            $table->unsignedInteger('best_seller_rank')->default(100);
            $table->string('silhouette');
            $table->string('base_color', 20);
            $table->string('accent_color', 20);
            $table->string('ink_color', 20);
            $table->string('motif');
            $table->string('sub_motif');
            $table->json('specs');
            $table->json('sizes');
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
