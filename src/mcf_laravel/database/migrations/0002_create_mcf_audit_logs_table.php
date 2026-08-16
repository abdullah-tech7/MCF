<?php

declare(strict_types=1);

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
        Schema::create('audit_logs', function (Blueprint $table): void {

            $table->id();

            $table->unsignedBigInteger('user_id')
                ->nullable();

            $table->string('user_role')
                ->nullable();

            $table->string('route_name')
                ->nullable();

            $table->string('action', 100);

            $table->text('description');

            $table->json('data')
                ->nullable();

            $table->string('ip_address', 45)
                ->nullable();

            $table->text('user_agent')
                ->nullable();

            $table->timestamp('created_at')
                ->useCurrent();

            $table->index(
                'user_id',
                'idx_audit_user',
            );

            $table->index(
                'action',
                'idx_audit_action',
            );

            $table->index(
                'created_at',
                'idx_audit_created',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};