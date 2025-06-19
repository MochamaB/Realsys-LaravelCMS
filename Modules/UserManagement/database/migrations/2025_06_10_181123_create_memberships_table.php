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
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->string('membership_number')->unique();
            $table->unsignedBigInteger('user_id');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('active'); // e.g., 'Active', 'Expired', 'Suspended'
            $table->string('payment_status')->nullable();
            $table->string('membership_type')->default('regular'); // e.g., 'Regular', 'Lifetime', 'Honorary'
            $table->decimal('fee_amount', 10, 2)->nullable();
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->boolean('issued_card')->default(false);
            $table->date('card_issue_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
