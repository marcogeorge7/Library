<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * copies.is_printed was declared as a `string` column (default '0') despite being
 * cast/treated as a boolean everywhere in code (Copy::$casts). Verified the real data
 * only ever contains the single value '0', so this converts cleanly.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('copies', function (Blueprint $table) {
            $table->boolean('is_printed_tmp')->default(false)->after('is_printed');
        });

        DB::table('copies')->update([
            'is_printed_tmp' => DB::raw("CASE WHEN is_printed IN ('1', 'true', 'TRUE') THEN 1 ELSE 0 END"),
        ]);

        Schema::table('copies', function (Blueprint $table) {
            $table->dropColumn('is_printed');
        });

        Schema::table('copies', function (Blueprint $table) {
            $table->renameColumn('is_printed_tmp', 'is_printed');
        });
    }

    public function down(): void
    {
        Schema::table('copies', function (Blueprint $table) {
            $table->string('is_printed_tmp')->default('0')->after('is_printed');
        });

        DB::table('copies')->update([
            'is_printed_tmp' => DB::raw("CASE WHEN is_printed = 1 THEN '1' ELSE '0' END"),
        ]);

        Schema::table('copies', function (Blueprint $table) {
            $table->dropColumn('is_printed');
        });

        Schema::table('copies', function (Blueprint $table) {
            $table->renameColumn('is_printed_tmp', 'is_printed');
        });
    }
};
