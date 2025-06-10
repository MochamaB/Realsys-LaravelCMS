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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('id_passport_number')->nullable();
            $table->string('membership_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('postal_address')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('gender')->nullable();
            $table->foreignId('ethnicity_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('special_status_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ncpwd_number')->nullable();
            $table->foreignId('religion_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('mobile_provider_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('county_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('constituency_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ward_id')->nullable()->constrained()->nullOnDelete();
            $table->date('enlisting_date')->nullable();
            $table->string('recruiting_person')->nullable();
            $table->foreignId('profile_type_id')->constrained();
            $table->json('additional_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
