<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrowers', function (Blueprint $table) {
            $table->string('address')->nullable()->after('phone');
            $table->string('national_id')->nullable()->unique()->after('phone');
        });

        Schema::table('borrowers', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        Schema::table('borrowers', function (Blueprint $table) {
            $table->unique('phone');
        });
    }

    public function down(): void
    {
        Schema::table('borrowers', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropUnique(['national_id']);
            $table->dropColumn(['address', 'national_id']);
        });

        Schema::table('borrowers', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
