<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mcf_storage', function (Blueprint $table) {
            $table->id();

            // MCF Storage Reference
            $table->string('reference', 255)->unique();

            // Original file information
            $table->string('original_name', 255);
            $table->string('extension', 20);
            $table->string('type', 50);
            $table->string('mime_type', 255);

            // File size in bytes
            $table->unsignedBigInteger('size');

            // Logical storage organization
            $table->string('folder', 255);

            // Selected storage provider and its root/bucket identifier
            $table->string('provider', 100);
            $table->string('storage_root', 255);

            // Access policy: public / protected
            $table->string('access', 20)->default('protected');

            $table->timestamps();

            // Query indexes
            $table->index('folder');
            $table->index('type');
            $table->index('provider');
            $table->index('storage_root');
            $table->index('access');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcf_storage');
    }
};