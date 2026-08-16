<?php

declare (strict_types = 1);

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
        
           /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */
        
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        
        
        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        Schema::create('users', function (Blueprint $table) {

            $table->id();
            
            $table->foreignId('role_id')
                ->constrained('roles')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('name');

            $table->string('email')
                ->unique();

            $table->string('phone', 30)
                ->nullable();

            $table->timestamp('email_verified_at')
                ->nullable();

            $table->timestamp('phone_verified_at')
                ->nullable();

            $table->string('password');

            $table->boolean('is_active')
                ->default(true);

            $table->timestamp('last_login_at')
                ->nullable();

            $table->rememberToken();

            $table->timestamps();

            $table->softDeletes();

            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('deletion_type')
                ->nullable();

            $table->timestamp('deletion_expires_at')
                ->nullable();

            $table->timestamp('restored_at')
                ->nullable();

            $table->foreignId('restored_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | Auth verifications
        |--------------------------------------------------------------------------
        */
        Schema::create('verification_requests', function (Blueprint $table): void {
            $table->id();

            /*
             |--------------------------------------------------------------------------
             | User
             |--------------------------------------------------------------------------
             */

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
             |--------------------------------------------------------------------------
             | Verification
             |--------------------------------------------------------------------------
             */

            // VerificationType::value
            $table->string('type', 50);

            // VerificationChannel::value
            $table->string('channel', 20);

            // VerificationMethod::value
            $table->string('method', 20);

            /*
             |--------------------------------------------------------------------------
             | Target
             |--------------------------------------------------------------------------
             */

            // Email address or phone number that received the verification.
            $table->string('target');

            /*
             |--------------------------------------------------------------------------
             | Secret
             |--------------------------------------------------------------------------
             */

            // Hashed verification code.
            $table->string('code_hash')->nullable();

            // Hashed verification token.
            $table->string('token_hash')->nullable();

            /*
             |--------------------------------------------------------------------------
             | Sending
             |--------------------------------------------------------------------------
             */

            // Number of successful send attempts.
            $table->unsignedSmallInteger('send_attempts')
                ->default(1);

            // Last successful send time.
            $table->timestamp('last_sent_at')
                ->nullable();

            /*
             |--------------------------------------------------------------------------
             | Lifecycle
             |--------------------------------------------------------------------------
             */

            // Request expiration.
            $table->timestamp('expires_at');

            // Successfully verified.
            $table->timestamp('verified_at')
                ->nullable();

            // Revoked by creating another request of the same type.
            $table->timestamp('revoked_at')
                ->nullable();

            $table->timestamps();

            /*
             |--------------------------------------------------------------------------
             | Indexes
             |--------------------------------------------------------------------------
             */

            $table->index('user_id');
            $table->index('type');
            $table->index('channel');
            $table->index('method');
            $table->index('target');
            $table->index('expires_at');
            $table->index('verified_at');
            $table->index('revoked_at');

            // Primary lookup used by Authentication.
            $table->index([
                'user_id',
                'type',
                'verified_at',
                'revoked_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_requests');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
    }
};
