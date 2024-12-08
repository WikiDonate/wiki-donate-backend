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
        Schema::create('section_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('section_id')->constrained('sections')->onDelete('cascade');
            $table->text('content');
            $table->unsignedInteger('version_number');
            $table->unsignedBigInteger('updated_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_versions');
    }
};
