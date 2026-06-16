<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('tin')->nullable()->after('registration_no');
            $table->string('sst_registration_no')->nullable()->after('tin');
            $table->string('msic_code', 10)->nullable()->after('sst_registration_no');
            $table->string('business_activity_desc')->nullable()->after('msic_code');
            $table->string('address_city')->nullable()->after('address');
            $table->string('address_postcode', 10)->nullable()->after('address_city');
            $table->string('address_state_code', 2)->nullable()->after('address_postcode');
            $table->string('address_country_code', 3)->default('MYS')->after('address_state_code');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'tin',
                'sst_registration_no',
                'msic_code',
                'business_activity_desc',
                'address_city',
                'address_postcode',
                'address_state_code',
                'address_country_code',
            ]);
        });
    }
};
