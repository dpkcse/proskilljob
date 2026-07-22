<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('education_institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable();      // University / College / School / etc
            $table->string('district')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false); // top 500 colleges mark
            $table->timestamps();

            // duplicate আটকাতে
            $table->unique(['name', 'type', 'district']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_institutions');
    }
};
