<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Add normalized transaction context without changing legacy sales data. */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('id')->constrained('customers')->nullOnDelete();
            }
            if (!Schema::hasColumn('sales', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('sales', 'payment_method')) {
                $table->string('payment_method', 20)->nullable()->after('change')->index();
            }
            if (!Schema::hasColumn('sales', 'order_source')) {
                $table->string('order_source', 20)->nullable()->after('payment_method')->index();
            }
            if (!Schema::hasColumn('sales', 'table_id')) {
                $table->foreignId('table_id')->nullable()->after('order_source')->constrained('tables')->nullOnDelete();
            }
        });
    }

    /** Intentionally non-destructive: transaction context must survive rollbacks. */
    public function down(): void
    {
        // No-op.
    }
};
