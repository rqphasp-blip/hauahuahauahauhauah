<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained('lead_campaigns')->cascadeOnDelete();
            $table->string('lead_name');
            $table->string('lead_email');
            $table->string('lead_phone')->nullable();
            $table->text('lead_message')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_leads');
    }
};