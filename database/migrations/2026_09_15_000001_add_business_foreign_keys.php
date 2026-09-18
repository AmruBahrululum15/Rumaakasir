<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'categories', 'products', 'suppliers', 'purchases', 'purchase_details',
        'sales', 'sale_details', 'customers', 'tables', 'settings',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tenantTable) {
            Schema::table($tenantTable, function (Blueprint $table) use ($tenantTable): void {
                $table->foreign('business_id', $tenantTable.'_business_id_fk')
                    ->references('id')
                    ->on('businesses')
                    ->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tenantTable) {
            Schema::table($tenantTable, function (Blueprint $table) use ($tenantTable): void {
                $table->dropForeign($tenantTable.'_business_id_fk');
            });
        }
    }
};
