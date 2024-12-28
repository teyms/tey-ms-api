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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->references('id')->on('users')
                    ->onDelete('set null');
                    // When a user is deleted, their user_id in activity_logs becomes NULL
                    // Example:
                    // Before user deletion:
                    // activity_logs: { id: 1, user_id: 5, ip_address: '192.168.1.1' }
                    // After user deletion:
                    // activity_logs: { id: 1, user_id: NULL, ip_address: '192.168.1.1' }
                    
            $table->string('ip_address'); // For both guests and authenticated users
            $table->string('user_agent')->nullable();
            $table->string('method'); // GET, POST, PUT, DELETE, etc.
            $table->string('path'); // API path
            $table->string('controller')->nullable();
            $table->string('action')->nullable(); // Controller method/function
            $table->json('request_data')->nullable(); // Request payload
            $table->json('response_data')->nullable(); // Response payload
            $table->integer('response_status')->nullable();
            $table->text('error_message')->nullable();            
            $table->timestamps();

            // Add index for common queries
            $table->index('ip_address');
            $table->index('user_id');
            $table->index('path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
