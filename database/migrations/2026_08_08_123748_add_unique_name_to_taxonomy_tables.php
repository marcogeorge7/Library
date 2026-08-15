<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * categories/authors/publishers/revisors/translators had no uniqueness enforced
 * anywhere except inside the Excel import path -- every admin "Add X" quick-action
 * across the app was a bare, unvalidated TextInput, so two admins (or one admin
 * twice) could freely create true duplicates. Verified 0 existing duplicates in the
 * current data before adding these, so this applies cleanly.
 *
 * `series` is deliberately NOT included: series names are allowed to repeat across
 * different categories by design (see BooksImport::getOrCreateSeries(), scoped to
 * name+category rather than name alone) -- 13 series currently share a name with
 * another series in a different category, and that's correct, not a defect.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['categories', 'authors', 'publishers', 'revisors', 'translators'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->unique('name', "{$table}_name_unique");
            });
        }
    }

    public function down(): void
    {
        foreach (['categories', 'authors', 'publishers', 'revisors', 'translators'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropUnique("{$table}_name_unique");
            });
        }
    }
};
