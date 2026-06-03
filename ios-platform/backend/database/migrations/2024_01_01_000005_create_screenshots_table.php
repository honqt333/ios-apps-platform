<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screenshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_id')->constrained('apps')->cascadeOnDelete();
            $table->string('path');
            $table->string('disk', 32)->default('public');
            $table->string('url')->nullable();
            $table->string('device_type', 16)->default('iphone'); // iphone, ipad
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['app_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screenshots');
    }
};
