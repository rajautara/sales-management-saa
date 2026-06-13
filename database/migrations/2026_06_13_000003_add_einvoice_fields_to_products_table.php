<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // LHDN e-Invoice classification code (e.g. "022" = Others).
            $table->string('classification_code', 10)->nullable()->after('tax_rate');
            // Standard unit of measurement code (e.g. "UNT", "KGM").
            $table->string('uom_code', 10)->nullable()->after('classification_code');
            // Tax type code (e.g. "01" = Sales Tax, "06" = Not Applicable, "E" = Exempt).
            $table->string('tax_type', 5)->nullable()->after('uom_code');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['classification_code', 'uom_code', 'tax_type']);
        });
    }
};
