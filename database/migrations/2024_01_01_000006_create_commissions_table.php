<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');
            
            $table->enum('commission_type', ['percentage', 'fixed', 'tiered']);
            $table->decimal('commission_value', 15, 2); // % atau nominal fixed
            $table->decimal('min_transaction', 15, 2)->default(0);
            $table->decimal('max_transaction', 15, 2)->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['role_id', 'is_active']);
        });

        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_item_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            
            $table->string('commission_type'); // sales_commission, leader_bonus, manager_bonus
            $table->decimal('transaction_amount', 15, 2);
            $table->decimal('base_price', 15, 2);
            $table->decimal('refund_amount', 15, 2); // unit_price - base_price (bisa minus)
            
            $table->decimal('commission_rate', 10, 2)->nullable(); // % atau nominal
            $table->decimal('commission_amount', 15, 2);
            
            $table->text('notes')->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();
            
            $table->index(['user_id', 'invoice_id']);
            $table->index('calculated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('commission_rules');
    }
};
