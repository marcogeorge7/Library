<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('copies', function (Blueprint $table) {
            $table->id();
            $table->string('barcode');
            $table->unsignedBigInteger('edition_id');
            $table->boolean('is_borrowed')->default(false);
            $table->string('is_printed')->default(false);

            $table->foreign('edition_id')->references('id')->on('editions')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('copies');
    }
};
