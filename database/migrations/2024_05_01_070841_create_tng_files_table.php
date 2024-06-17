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
        Schema::create('tng_file_convert', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->binary('content'); // Binary file storage
            $table->integer('size')->unsigned()->comment('bytes'); // Size of the file in bytes
            $table->string('type')->comment('MIME Types');
            $table->string('converted_name')->nullable();
            $table->binary('converted_content')->nullable(); // Binary file storage
            $table->integer('converted_size')->unsigned()->nullable()->comment('bytes'); // Size of the file in bytes
            $table->string('converted_type')->nullable()->comment('MIME Types');
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tng_file_convert');
    }
};
