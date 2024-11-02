<?php

use App\Models\Edition;
use App\Models\Translator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('edition_translator_pivot_table', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Edition::class);
            $table->foreignIdFor(Translator::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edition_translator_pivot');
    }
};
