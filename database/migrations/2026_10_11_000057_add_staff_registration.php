<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Código secreto por clínica para el enlace de registro del personal.
        Schema::table('tenants', function ($table) {
            $table->string('invite_code', 40)->nullable()->unique();
        });

        // Una solicitud pendiente todavía no tiene rol.
        DB::statement('ALTER TABLE tenant_users ALTER COLUMN role_id DROP NOT NULL');

        foreach (DB::table('tenants')->whereNull('invite_code')->pluck('id') as $id) {
            DB::table('tenants')->where('id', $id)->update(['invite_code' => Str::random(32)]);
        }
    }

    public function down(): void
    {
        DB::table('tenant_users')->whereNull('role_id')->delete();
        DB::statement('ALTER TABLE tenant_users ALTER COLUMN role_id SET NOT NULL');
        Schema::table('tenants', fn ($t) => $t->dropColumn('invite_code'));
    }
};
