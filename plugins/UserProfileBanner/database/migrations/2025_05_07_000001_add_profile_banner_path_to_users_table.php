<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if the users table exists before trying to add a column
        if (Schema::hasTable("users")) {
            Schema::table("users", function (Blueprint $table) {
                // Add the new column only if it doesn't already exist
                if (!Schema::hasColumn("users", "profile_banner_path")) {
                    $table->string("profile_banner_path")->nullable()->after("profile_photo_path"); // Adjust 'after' as needed
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable("users")) {
            Schema::table("users", function (Blueprint $table) {
                if (Schema::hasColumn("users", "profile_banner_path")) {
                    $table->dropColumn("profile_banner_path");
                }
            });
        }
    }
};

