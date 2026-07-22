<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class CandidateSkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $candidates = Candidate::all();

        // Create some default skills if none exist
        if (Skill::count() === 0) {
            $skills = [
                'PHP', 'JavaScript', 'Python', 'Java', 'C++',
                'HTML', 'CSS', 'React', 'Vue.js', 'Angular',
                'Laravel', 'Node.js', 'MySQL', 'PostgreSQL', 'MongoDB',
            ];

            foreach ($skills as $skill) {
                Skill::create(['name' => $skill]);
            }
        }

        $skills = Skill::all();

        if ($skills->count() > 0) {
            foreach ($candidates as $candidate) {
                $randomSkills = $skills->random(min(2, $skills->count()));
                $candidate->skills()->attach($randomSkills);
            }
        }
    }
}
