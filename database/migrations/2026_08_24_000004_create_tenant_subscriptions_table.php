<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_subscriptions', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('plan_id')->constrained('plans');
                    $table->date('start_date');
                    $table->date('end_date')->nullable();
                    $table->string('status', 20)->default('active');
                    $table->decimal('amount_paid', 10, 2)->nullable();
                    $table->string('payment_method', 30)->nullable();
                    $table->timestampTz('created_at')->useCurrent();
                    $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_subscriptions');
    }
};
