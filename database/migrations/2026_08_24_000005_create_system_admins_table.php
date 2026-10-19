<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_admins', function (Blueprint $table) {
                    $table->id();
                    $table->string('name', 120);
                    $table->string('email', 150)->unique();
                    $table->string('password');
                    $table->rememberToken();
                    $table->timestampTz('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_admins');
    }
};
