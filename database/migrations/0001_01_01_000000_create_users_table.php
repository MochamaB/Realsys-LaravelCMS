<?php

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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Name Fields
            $table->string('first_name');
            $table->string('surname')->nullable();
            $table->string('last_name')->nullable();


            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();


                // Contact Information
            $table->string('phone_number', 20)->nullable();
            
            // Identification
            $table->string('id_number', 100)->nullable();
            
            // Account Status
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])->default('pending');
            
            // Force password change on first login
            $table->boolean('must_change_password')->default(false);
            
           
            
            // Social Login
            $table->string('provider',50)->nullable()->comment('e.g., google, facebook, github');
            $table->string('provider_id',191)->nullable();
            
            $table->text('provider_token')->nullable();
            $table->text('provider_refresh_token')->nullable();
            
            // Password Reset
            $table->string('password_reset_token')->nullable();
            $table->timestamp('password_reset_token_expires_at')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['provider', 'provider_id']);
            $table->index('status');
            $table->index('email_verified_at');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
