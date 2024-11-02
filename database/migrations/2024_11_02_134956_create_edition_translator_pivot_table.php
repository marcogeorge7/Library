<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edition_translator_pivot_table', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('edition_id');
            $table->unsignedBigInteger('translator_id');

            $table->foreign('edition_id')->references('id')->on('editions')->onDelete('cascade');
            $table->foreign('translator_id')->references('id')->on('translators')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edition_translator_pivot');
    }
};
