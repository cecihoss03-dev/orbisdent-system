<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_subscriptions', function (Blueprint $table) {
            $table->timestampTz('expiry_alert_sent_at')->nullable()->after('payment_method');
            $table->index(['status', 'end_date'], 'idx_tenant_subs_status_end');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_subscriptions', function (Blueprint $table) {
            $table->dropIndex('idx_tenant_subs_status_end');
            $table->dropColumn('expiry_alert_sent_at');
        });
    }
};
