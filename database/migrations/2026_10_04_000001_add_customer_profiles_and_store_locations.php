<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up():void {
        Schema::table('customer_accounts',function(Blueprint $t){$t->string('avatar_path')->nullable();$t->date('birth_date')->nullable();});
        Schema::table('businesses',function(Blueprint $t){$t->decimal('latitude',10,7)->nullable();$t->decimal('longitude',10,7)->nullable();});
        Schema::create('customer_addresses',function(Blueprint $t){
            $t->id();$t->foreignId('customer_account_id')->constrained();
            foreach(['label','recipient_name','phone','province','city','district','village','postal_code'] as $f)$t->string($f)->nullable();
            $t->text('address');$t->text('notes')->nullable();$t->decimal('latitude',10,7)->nullable();$t->decimal('longitude',10,7)->nullable();$t->boolean('is_default')->default(false);$t->timestamps();
        });
    }
    public function down():void { /* Preserve customer data; rollback requires an explicit data retention review. */ }
};
