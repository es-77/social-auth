<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Google fields
            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->unique()->after('id');
            }
            
            // Microsoft fields (for future use)
            if (!Schema::hasColumn('users', 'microsoft_id')) {
                $table->string('microsoft_id')->nullable()->unique()->after('id');
            }
            
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'google_token')) {
                $table->text('google_token')->nullable()->after('avatar');
            }
            if (!Schema::hasColumn('users', 'google_refresh_token')) {
                $table->text('google_refresh_token')->nullable()->after('google_token');
            }
            
            // Microsoft tokens (for future use)
            if (!Schema::hasColumn('users', 'microsoft_token')) {
                $table->text('microsoft_token')->nullable()->after('google_refresh_token');
            }
            if (!Schema::hasColumn('users', 'microsoft_refresh_token')) {
                $table->text('microsoft_refresh_token')->nullable()->after('microsoft_token');
            }
            
            // Make password nullable for social login users
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'google_id', 
                'microsoft_id', 
                'avatar', 
                'google_token', 
                'google_refresh_token',
                'microsoft_token',
                'microsoft_refresh_token'
            ]);
        });
    }
};