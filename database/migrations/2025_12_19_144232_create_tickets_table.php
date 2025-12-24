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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_id')->unique()->nullable(); // Firestore document ID
            $table->string('title');
            $table->text('description');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('status')->default('open');
            $table->string('priority')->default('medium');
            $table->json('attachments')->nullable(); // Array of file paths
            $table->timestamps();
            $table->softDeletes(); // Untuk tracking deleted tickets

            $table->index(['status', 'priority']);
            $table->index(['customer_id', 'status']);
            $table->index('agent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
