<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->integer('partCode');
            $table->unsignedBigInteger('publisher_id');
            $table->string('publish_year')->nullable();
            $table->string('lang')->default('ar');
            $table->string('cover')->nullable();
            $table->boolean('internal_borrowing');

            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('publisher_id')->references('id')->on('publishers')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('editions');
    }
};
