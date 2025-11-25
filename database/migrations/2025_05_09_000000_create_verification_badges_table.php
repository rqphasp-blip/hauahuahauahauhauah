<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('alt_text');
            $table->string('icon_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_badges');
    }
};