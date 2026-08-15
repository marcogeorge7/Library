<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('print_job_copy', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('copy_id')->constrained('copies')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_job_copy');
        Schema::dropIfExists('print_jobs');
    }
};
