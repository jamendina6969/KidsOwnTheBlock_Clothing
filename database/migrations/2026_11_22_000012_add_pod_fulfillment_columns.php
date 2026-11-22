<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('printful_variant_id')->nullable()->after('sub_motif');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('country', 10)->default('PH')->after('city');
            $table->string('fulfillment_provider', 30)->default('local_ph')->after('country');
            $table->string('external_pod_id')->nullable()->after('fulfillment_provider');
            $table->string('pod_status')->nullable()->after('external_pod_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['country', 'fulfillment_provider', 'external_pod_id', 'pod_status']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('printful_variant_id');
        });
    }
};
