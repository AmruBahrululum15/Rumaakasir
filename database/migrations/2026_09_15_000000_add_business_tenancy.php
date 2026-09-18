<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private array $tenantTables = [
        'users', 'categories', 'products', 'suppliers', 'purchases',
        'purchase_details', 'sales', 'sale_details', 'customers', 'tables', 'settings',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('businesses')) {
            Schema::create('businesses', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('phone', 50)->nullable();
                $table->text('address')->nullable();
                $table->string('logo')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // A single default business keeps all legacy records intact and tenant-owned.
        $defaultBusinessId = DB::table('businesses')->orderBy('id')->value('id');
        if ($defaultBusinessId === null) {
            $defaultBusinessId = DB::table('businesses')->insertGetId([
                'name' => 'Toko Default',
                'slug' => 'toko-default-'.Str::lower(Str::random(8)),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($this->tenantTables as $tenantTable) {
            if (! Schema::hasTable($tenantTable)) {
                continue;
            }

            if (! Schema::hasColumn($tenantTable, 'business_id')) {
                Schema::table($tenantTable, function (Blueprint $table) {
                    // Kept nullable for a non-destructive upgrade. The model layer requires
                    // a tenant context for all new operational records.
                    $table->unsignedBigInteger('business_id')->nullable()->index();
                });
            }

            DB::table($tenantTable)->whereNull('business_id')->update(['business_id' => $defaultBusinessId]);
        }

        $ownerId = DB::table('users')->orderBy('id')->value('id');
        if ($ownerId !== null) {
            DB::table('businesses')->where('id', $defaultBusinessId)->update(['owner_id' => $ownerId]);
        }

        // These values were globally unique before tenancy. Make their uniqueness tenant-aware.
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->unique(['business_id', 'slug']);
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->unique(['business_id', 'code']);
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->unique(['business_id', 'code']);
        });
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['key']);
            $table->unique(['business_id', 'key']);
        });
    }

    public function down(): void
    {
        foreach (array_reverse($this->tenantTables) as $tenantTable) {
            if (! Schema::hasTable($tenantTable) || ! Schema::hasColumn($tenantTable, 'business_id')) {
                continue;
            }

            Schema::table($tenantTable, function (Blueprint $table) {
                $table->dropForeign(['business_id']);
                $table->dropColumn('business_id');
            });
        }

        Schema::dropIfExists('businesses');
    }
};
