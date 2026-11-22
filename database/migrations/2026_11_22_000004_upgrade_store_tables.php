<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('front_image')->nullable()->after('sub_motif');
            $table->string('back_image')->nullable()->after('front_image');
            $table->string('size_chart_image')->nullable()->after('back_image');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable()->after('customer_email');
            $table->string('phone')->nullable()->after('customer_name');
            $table->string('shipping_address')->nullable()->after('phone');
            $table->string('city')->nullable()->after('shipping_address');
            $table->text('notes')->nullable()->after('city');
            $table->decimal('shipping_fee', 8, 2)->default(0)->after('subtotal');
            $table->decimal('total', 10, 2)->default(0)->after('shipping_fee');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['customer_name', 'phone', 'shipping_address', 'city', 'notes', 'shipping_fee', 'total']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['front_image', 'back_image', 'size_chart_image']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
};
