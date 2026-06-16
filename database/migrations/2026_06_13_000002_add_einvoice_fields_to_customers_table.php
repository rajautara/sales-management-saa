<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('tin')->nullable()->after('tax_no');
            $table->string('registration_type', 20)->nullable()->after('tin');
            $table->string('registration_no')->nullable()->after('registration_type');
            $table->string('sst_registration_no')->nullable()->after('registration_no');
            $table->string('address_city')->nullable()->after('billing_address');
            $table->string('address_postcode', 10)->nullable()->after('address_city');
            $table->string('address_state_code', 2)->nullable()->after('address_postcode');
            $table->string('address_country_code', 3)->default('MYS')->after('address_state_code');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'tin',
                'registration_type',
                'registration_no',
                'sst_registration_no',
                'address_city',
                'address_postcode',
                'address_state_code',
                'address_country_code',
            ]);
        });
    }
};
