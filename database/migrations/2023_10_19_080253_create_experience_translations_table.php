<?php

use App\Models\Experience;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('experience_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Experience::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('locale');
            $table->timestamps();
        });

        \Artisan::call('db:seed --class=ExperienceTranslationSeeder --force');

        Schema::table('experiences', function (Blueprint $table) {
            $table->dropColumn(['name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->string('name')->nullable();
        });

        \DB::table('experience_translations')
            ->orderByRaw('CASE WHEN locale = ? THEN 0 ELSE 1 END', [config('app.locale', 'en')])
            ->get()
            ->unique('experience_id')
            ->each(function ($translation) {
                \DB::table('experiences')
                    ->where('id', $translation->experience_id)
                    ->update(['name' => $translation->name]);
            });

        Schema::dropIfExists('experience_translations');
    }
};
