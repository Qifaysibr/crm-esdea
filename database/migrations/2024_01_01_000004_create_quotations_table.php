<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number')->unique(); // QT-095/Esdea/XII/2025
            $table->foreignId('lead_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('customer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_company')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            $table->date('quotation_date');
            $table->date('valid_until'); // Auto 14 hari
            
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0); // Diskon global %
            $table->decimal('discount_amount', 15, 2)->default(0); // Diskon global nominal
            $table->decimal('total', 15, 2)->default(0);
            
            $table->enum('status', ['draft', 'sent', 'approved', 'rejected', 'converted'])->default('draft');
            $table->text('notes')->nullable();
            $table->text('terms')->nullable(); // Syarat & ketentuan
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'created_by']);
            $table->index('quotation_date');
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            
            $table->integer('line_order')->default(0);
            $table->string('product_name');
            $table->text('description')->nullable();
            $table->string('notes')->nullable(); // Keterangan kolom
            
            $table->decimal('unit_price', 15, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('discount_percentage', 5, 2)->default(0); // Diskon per baris
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2);
            
            $table->timestamps();
            
            $table->index('quotation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
    }
};
