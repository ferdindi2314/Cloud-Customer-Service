<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'assigned' to the status enum. This uses raw SQL because altering ENUMs
        // requires a table modify in MySQL.
        DB::statement("ALTER TABLE `tickets` MODIFY `status` ENUM('open','assigned','in_progress','resolved','closed') NOT NULL DEFAULT 'open'");
    }

    public function down(): void
    {
        // Revert by removing 'assigned' (if present) — note: existing rows with 'assigned'
        // will be converted to the default 'open' by MySQL when the enum is modified back.
        DB::statement("ALTER TABLE `tickets` MODIFY `status` ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open'");
    }
};
