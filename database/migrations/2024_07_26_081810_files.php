<?php

declare(strict_types=1);

use App\Models\FileCategory;
use App\Models\FileType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('file_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('file_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('files', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignIdFor(FileType::class);
            $table->foreignIdFor(FileCategory::class);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('file_versions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('path');
            $table->enum('status', [
                'draft',
                'active',
                'inactive',
            ]);
            $table->foreignIdFor(File::class);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_categories');
        Schema::dropIfExists('files');
        Schema::dropIfExists('file_versions');
        Schema::dropIfExists('file_types');
    }
};
