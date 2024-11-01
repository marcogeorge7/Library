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
            $table->string('name')->nullable();
            $table->string('slug');
            $table->foreignId('book_id');
            $table->integer('partCode');
            $table->foreignId('publisher_id');
            $table->string('publish_year');
            $table->string('lang');
            $table->string('cover');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('editions');
    }
};
