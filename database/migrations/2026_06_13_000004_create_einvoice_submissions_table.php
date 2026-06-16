<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('einvoice_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->default('invoice');
            $table->string('status', 20)->default('pending');
            $table->string('uuid')->nullable();
            $table->string('long_id')->nullable();
            $table->string('submission_uid')->nullable();
            $table->string('validation_link')->nullable();
            $table->text('qr_payload')->nullable();
            $table->string('document_hash')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->json('error_log')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('einvoice_submissions');
    }
};
