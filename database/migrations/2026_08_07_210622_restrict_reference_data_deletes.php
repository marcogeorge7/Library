<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Force-deleting a Category, Series, or Publisher used to cascade-delete every
 * dependent Book/Edition/Copy/BorrowRequest beneath it (entire borrow history
 * included), with no warning beyond Filament's generic confirm dialog. These three
 * are shared reference/lookup data, not owned children -- restrict deletion instead
 * so an admin has to actually reassign or remove the dependents first. The
 * books->editions->copies->borrow_requests chain stays cascade: those genuinely are
 * owned children of their parent record.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['series_id']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
            $table->foreign('series_id')->references('id')->on('series')->onDelete('restrict');
        });

        Schema::table('editions', function (Blueprint $table) {
            $table->dropForeign(['publisher_id']);
        });

        Schema::table('editions', function (Blueprint $table) {
            $table->foreign('publisher_id')->references('id')->on('publishers')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['series_id']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('series_id')->references('id')->on('series')->onDelete('cascade');
        });

        Schema::table('editions', function (Blueprint $table) {
            $table->dropForeign(['publisher_id']);
        });

        Schema::table('editions', function (Blueprint $table) {
            $table->foreign('publisher_id')->references('id')->on('publishers')->onDelete('cascade');
        });
    }
};
