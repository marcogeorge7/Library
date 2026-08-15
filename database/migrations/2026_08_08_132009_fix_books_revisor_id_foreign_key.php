<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * books.revisor_id's FK constraint targeted users.id, despite Book::revisor() being
 * belongsTo(Revisor::class) -- a plain migration bug, not an intentional "revisor is
 * actually a user" design (no UserResource exists, nothing in the app ever sets this
 * column; verified 0 non-null values in the real data before writing this). Retarget
 * to revisors.id, and use restrict (not cascade) to match the other reference-data
 * FKs fixed this round -- a revisor is shared lookup data, not an owned child.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['revisor_id']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->foreign('revisor_id')->references('id')->on('revisors')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['revisor_id']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->foreign('revisor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
