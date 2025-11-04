<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->string('password')->nullable()->after('location');
            $table->rememberToken();
            $table->boolean('marketing_opt_in')->default(false)->after('password');
            $table->json('preferences')->nullable()->after('marketing_opt_in');
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn(['password', 'remember_token', 'marketing_opt_in', 'preferences']);
        });
    }
};

