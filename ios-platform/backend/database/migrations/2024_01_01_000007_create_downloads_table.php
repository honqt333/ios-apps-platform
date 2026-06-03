<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_id')->constrained('apps')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('app_file_id')->nullable()->constrained('app_files')->nullOnDelete();
            $table->string('version', 32)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('device_id', 128)->nullable();
            $table->string('country', 2)->nullable();
            $table->unsignedBigInteger('bytes_sent')->default(0);
            $table->unsignedInteger('status_code')->default(200);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['app_id', 'created_at']);
            $table->index('user_id');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('downloads');
    }
};
