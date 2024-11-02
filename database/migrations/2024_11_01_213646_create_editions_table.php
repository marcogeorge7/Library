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
            $table->foreignId('book_id');
            $table->integer('partCode');
            $table->foreignId('publisher_id');
            $table->string('publish_year')->nullable();
            $table->string('lang')->default('ar');
            $table->string('cover')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('editions');
    }
};
