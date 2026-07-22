<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('bd_district')->nullable()->after('whatsapp_number');
            $table->string('bd_thana')->nullable()->after('bd_district');
            $table->string('bd_post_office')->nullable()->after('bd_thana');
            $table->string('house_road_village')->nullable()->after('bd_post_office');
            $table->string('alternate_mobile_number')->nullable()->after('house_road_village');
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn([
                'bd_district',
                'bd_thana',
                'bd_post_office',
                'house_road_village',
                'alternate_mobile_number',
            ]);
        });
    }
};
