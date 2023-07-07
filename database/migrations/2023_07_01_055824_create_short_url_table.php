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
        Schema::create('short_url', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('ori_url');
            $table->string('ip_address')->nullable();
            $table->integer('used_count')->default(0);
            $table->dateTime('expired_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('short_url');
    }
};
