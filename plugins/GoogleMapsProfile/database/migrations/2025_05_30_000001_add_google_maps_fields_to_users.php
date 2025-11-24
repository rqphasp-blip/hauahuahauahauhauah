<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'feature_maps_status')) {
                    $table->boolean('feature_maps_status')->default(false);
                }

                if (!Schema::hasColumn('users', 'feature_maps_address')) {
                    $table->string('feature_maps_address')->nullable();
                }

                if (!Schema::hasColumn('users', 'feature_maps_coordinates')) {
                    $table->string('feature_maps_coordinates')->nullable();
                }

                if (!Schema::hasColumn('users', 'feature_maps_zoom')) {
                    $table->unsignedTinyInteger('feature_maps_zoom')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                foreach ([
                    'feature_maps_zoom',
                    'feature_maps_coordinates',
                    'feature_maps_address',
                    'feature_maps_status',
                ] as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};