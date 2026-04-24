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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('academy_id')->constrained()->onDelete('cascade');
            
            $table->decimal('amount', 15, 2);
            $table->decimal('platform_fee', 15, 2);
            $table->decimal('creator_revenue', 15, 2);
            
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('payment_gateway')->nullable();
            $table->string('reference_id')->nullable();
            
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
