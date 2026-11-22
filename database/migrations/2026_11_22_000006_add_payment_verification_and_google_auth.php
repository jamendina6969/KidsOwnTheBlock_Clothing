<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('is_admin');
            $table->string('avatar')->nullable()->after('google_id');
            $table->string('password')->nullable()->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status')->default('unpaid_cod')->after('payment_method');
            $table->string('payment_reference')->nullable()->after('payment_status');
            $table->string('payment_proof')->nullable()->after('payment_reference');
            $table->string('promo_code')->nullable()->after('notes');
            $table->decimal('discount_amount', 8, 2)->default(0)->after('subtotal');
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->string('status')->default('open')->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_reference', 'payment_proof', 'promo_code', 'discount_amount']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'avatar']);
        });
    }
};
