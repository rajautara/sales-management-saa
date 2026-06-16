<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('einvoice_submissions', function (Blueprint $table) {
            // One submission row per invoice — prevents duplicate rows from
            // concurrent submit attempts (two tabs / retries).
            $table->unique('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::table('einvoice_submissions', function (Blueprint $table) {
            $table->dropUnique(['invoice_id']);
        });
    }
};
