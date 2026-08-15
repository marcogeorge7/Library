<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            $table->string('old_code')->nullable()->after('part_name');
        });

        // Backfill: every edition that exists before this migration was created by
        // the pre-fix importer, which always paired exactly one Edition with exactly
        // one Book (1 spreadsheet row = 1 Book = 1 Edition). Copy the row's identity
        // down from books.old_code so bookAlreadyImported() -- which after this fix
        // reads editions.old_code exclusively -- keeps recognizing already-imported
        // rows on the next run instead of re-importing all of them as "new".
        // Uses the query builder (not a raw multi-table UPDATE) because the app's
        // default connection is sqlite, which doesn't support UPDATE ... JOIN.
        DB::table('books')
            ->whereNotNull('old_code')
            ->select('id', 'old_code')
            ->chunkById(500, function ($books) {
                foreach ($books as $book) {
                    DB::table('editions')
                        ->where('book_id', $book->id)
                        ->update(['old_code' => $book->old_code]);
                }
            });

        // Not book_id+partCode: real data has multiple Editions of the same part
        // sharing a partCode by design (e.g. the same part reprinted/re-edited by a
        // different publisher/author) -- confirmed against allbooks_v5.xlsx, where
        // rows R099/R099A and R100/R100A are two distinct editions of parts 1 and 2
        // of the same grouped book. old_code is the only per-row identity that's
        // actually unique in the source data.
        Schema::table('editions', function (Blueprint $table) {
            $table->unique('old_code', 'editions_old_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            $table->dropUnique('editions_old_code_unique');
            $table->dropColumn('old_code');
        });
    }
};
