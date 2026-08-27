<?php

use App\Models\Education;
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
        Schema::create('education_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Education::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('locale');
            $table->timestamps();
        });

        \Artisan::call('db:seed --class=EducationTranslationSeeder --force');

        Schema::table('education', function (Blueprint $table) {
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
        Schema::table('education', function (Blueprint $table) {
            $table->string('name')->nullable();
        });

        \DB::table('education_translations')
            ->orderByRaw('CASE WHEN locale = ? THEN 0 ELSE 1 END', [config('app.locale', 'en')])
            ->get()
            ->unique('education_id')
            ->each(function ($translation) {
                \DB::table('education')
                    ->where('id', $translation->education_id)
                    ->update(['name' => $translation->name]);
            });

        Schema::dropIfExists('education_translations');
    }
};
