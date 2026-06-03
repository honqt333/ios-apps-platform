<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_id')->constrained('apps')->cascadeOnDelete();
            $table->string('version', 32);
            $table->string('build_number', 32)->nullable();
            $table->string('disk', 32);
            $table->string('path');
            $table->string('manifest_path')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('checksum_sha256', 64)->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->index(['app_id', 'is_current']);
            $table->index('checksum_sha256');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_files');
    }
};
