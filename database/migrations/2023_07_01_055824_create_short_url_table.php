<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('short_url', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->references('id')->on('users');
            // $table->string('url')->unique();
            $table->string('guest_identifier')->nullable();
            $table->string('url');
            $table->text('original_url');
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->integer('click_count')->default(0);
            $table->dateTime('expires_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        // ensure either user_id or guest_identifier is present
        // Add check constraint using raw SQL
        DB::statement('ALTER TABLE short_url ADD CHECK (user_id IS NOT NULL OR guest_identifier IS NOT NULL)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('short_url');
    }
};
