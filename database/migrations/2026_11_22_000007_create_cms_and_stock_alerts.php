<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lookbooks', function (Blueprint $table) {
            $table->id();
            $table->string('collection_title');
            $table->string('season');
            $table->unsignedInteger('look_number')->default(1);
            $table->string('title');
            $table->string('garments');
            $table->string('image_path')->nullable();
            $table->string('bg_color', 20)->default('#6e0e0e');
            $table->string('drape_color', 20)->default('#3d0404');
            $table->string('sil_color', 20)->default('#0d0d0d');
            $table->string('acc_color', 20)->default('#ffffff');
            $table->timestamps();
        });

        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('subtitle');
            $table->string('duration', 20)->default('02:30');
            $table->string('date_label', 30)->default('NOV 2026');
            $table->string('tag', 30)->default('CAMPAIGN');
            $table->string('video_url')->nullable();
            $table->timestamps();
        });

        Schema::create('restock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->boolean('notified')->default(false);
            $table->timestamps();
        });

        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('restock_alerts');
        Schema::dropIfExists('stories');
        Schema::dropIfExists('lookbooks');
    }
};
