<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto"');
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pg_trgm"');
    }

    public function down(): void
    {
        // A propósito no se eliminan las extensiones en el rollback: otros
        // objetos de la base (índices GIN, gen_random_uuid()) podrían depender
        // de ellas. Si de verdad necesitas quitarlas, hazlo manualmente.
    }
};
