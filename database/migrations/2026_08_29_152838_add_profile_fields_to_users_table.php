<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan field profil user jika belum ada.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            if (!Schema::hasColumn('users', 'nik')) {
                $table->string('nik', 20)
                    ->nullable()
                    ->unique()
                    ->after('username');
            }

            if (!Schema::hasColumn('users', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')
                    ->nullable()
                    ->after('nik');
            }

            if (!Schema::hasColumn('users', 'alamat')) {
                $table->text('alamat')
                    ->nullable()
                    ->after('tanggal_lahir');
            }

            if (!Schema::hasColumn('users', 'no_telepon')) {
                $table->string('no_telepon', 20)
                    ->nullable()
                    ->after('alamat');
            }

            if (!Schema::hasColumn('users', 'foto')) {
                $table->string('foto')
                    ->nullable()
                    ->after('no_telepon');
            }
        });
    }

    /**
     * Hapus field profil user saat migration di-rollback.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            if (Schema::hasColumn('users', 'foto')) {
                $table->dropColumn('foto');
            }

            if (Schema::hasColumn('users', 'no_telepon')) {
                $table->dropColumn('no_telepon');
            }

            if (Schema::hasColumn('users', 'alamat')) {
                $table->dropColumn('alamat');
            }

            if (Schema::hasColumn('users', 'tanggal_lahir')) {
                $table->dropColumn('tanggal_lahir');
            }

            if (Schema::hasColumn('users', 'nik')) {
                $table->dropUnique(['nik']);
                $table->dropColumn('nik');
            }
        });
    }
};