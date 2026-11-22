<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('stock_qty')->default(24)->after('sold_out');
            $table->decimal('unit_cost', 8, 2)->default(18.00)->after('price');
            $table->unsignedInteger('lead_time_days')->default(7)->after('stock_qty');
            $table->decimal('ordering_cost', 8, 2)->default(45.00)->after('lead_time_days');
            $table->decimal('holding_cost_rate', 5, 2)->default(0.20)->after('ordering_cost');
        });

        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedInteger('discount_percent');
            $table->boolean('active')->default(true);
            $table->unsignedInteger('uses_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['stock_qty', 'unit_cost', 'lead_time_days', 'ordering_cost', 'holding_cost_rate']);
        });
    }
};
