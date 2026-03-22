<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('otp_account_id')->constrained()->cascadeOnDelete();
            $table->string('message_id')->nullable()->index();
            $table->string('sender_email')->nullable();
            $table->string('subject')->nullable();
            $table->text('email_snippet')->nullable();
            $table->string('otp_code')->nullable();
            $table->string('fetched_status')->default('success');
            $table->timestamp('received_at')->nullable()->index();
            $table->longText('raw_payload')->nullable();
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_messages');
    }
};