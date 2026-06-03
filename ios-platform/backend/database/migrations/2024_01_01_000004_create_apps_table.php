<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apps', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('developer');
            $table->text('description')->nullable();
            $table->longText('long_description')->nullable();

            // iOS Metadata
            $table->string('bundle_id');
            $table->string('version', 32);
            $table->string('build_number', 32)->nullable();
            $table->string('minimum_ios_version', 16)->default('14.0');
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->string('file_size_human', 32)->nullable();

            // Categorization
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            // Storage references
            $table->string('icon_path')->nullable();
            $table->string('icon_url')->nullable();
            $table->string('ipa_path')->nullable();
            $table->string('ipa_disk', 32)->default('local');
            $table->string('manifest_path')->nullable();
            $table->unsignedBigInteger('ipa_size_bytes')->default(0);

            // Public install
            $table->string('install_token', 64)->nullable()->unique();
            $table->timestamp('install_token_expires_at')->nullable();
            $table->unsignedInteger('downloads_count')->default(0);

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_featured')->default(false);

            // Changelog
            $table->text('changelog')->nullable();
            $table->json('changelog_history')->nullable();

            // Localized
            $table->json('localized')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'is_archived', 'deleted_at']);
            $table->index(['category_id', 'is_active']);
            $table->index('bundle_id');
            $table->index('developer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apps');
    }
};
