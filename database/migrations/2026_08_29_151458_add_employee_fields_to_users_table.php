<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('nik', 30)
                ->nullable()
                ->unique()
                ->after('id');

            $table->date('tanggal_lahir')
                ->nullable()
                ->after('email');

            $table->text('alamat')
                ->nullable()
                ->after('tanggal_lahir');

            $table->string('no_telepon', 20)
                ->nullable()
                ->after('alamat');

            $table->string('foto')
                ->nullable()
                ->after('no_telepon');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropUnique([
                'nik'
            ]);

            $table->dropColumn([
                'nik',
                'tanggal_lahir',
                'alamat',
                'no_telepon',
                'foto',
            ]);
        });
    }
};