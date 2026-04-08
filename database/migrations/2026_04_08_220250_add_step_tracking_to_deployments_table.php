<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deployments', function (Blueprint $table) {
            $table->unsignedTinyInteger('current_step')->default(0)->after('status');
            $table->unsignedTinyInteger('total_steps')->default(0)->after('current_step');
        });
    }

    public function down(): void
    {
        Schema::table('deployments', function (Blueprint $table) {
            $table->dropColumn(['current_step', 'total_steps']);
        });
    }
};
