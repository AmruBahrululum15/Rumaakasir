<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {
    public function up(): void
    {
        // Audit both tables before modifying any index. Never rename existing accounts.
        foreach (['users' => 'username', 'store_registrations' => 'owner_username'] as $table => $column) {
            if (DB::table($table)->select($column)->groupBy($column)->havingRaw('COUNT(*) > 1')->exists()) {
                throw new RuntimeException('Duplicate usernames detected in '.$table.'. Resolve manually before migration.');
            }
        }
        foreach (['users' => ['email', 'username'], 'store_registrations' => ['owner_email', 'owner_username']] as $table => [$email, $username]) {
            $indexes = Schema::getIndexes($table);
            foreach ($indexes as $index) {
                if ($index['unique'] && ! $index['primary'] && $index['columns'] === [$email]) {
                    Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropUnique($index['name']));
                }
            }
            if (! collect($indexes)->contains(fn ($index) => $index['unique'] && $index['columns'] === [$username])) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->unique($username));
            }
        }
    }

    public function down(): void
    {
        // Shared email addresses cannot safely be made unique again. Preserve all data.
    }
};
