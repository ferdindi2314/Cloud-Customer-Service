<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support ENUM, so this is a no-op for SQLite
        // The 'assigned' status is already supported via string columns
    }

    public function down(): void
    {
        // No-op for SQLite
    }
};
