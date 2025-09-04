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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('parent')->nullable();
            $table->char("code")->nullable();
            $table->string('name');
            $table->string('type');
            $table->string('nim_label')->nullable();
            $table->string('level')->nullable();
            $table->boolean("is_active")->default(true);
            $table->boolean("is_satker")->default(false);
            $table->boolean("is_fakultas")->default(false);
            $table->boolean("is_prodi")->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};