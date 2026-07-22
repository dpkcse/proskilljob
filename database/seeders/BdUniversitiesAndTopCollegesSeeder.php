<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BdUniversitiesAndTopCollegesSeeder extends Seeder
{
    public function run(): void
    {
        $base = storage_path('app/seed/bd_edu');

        $uniFile = $base . '/universities.json';
        $colFile = $base . '/nu_colleges.json';

        if (!File::exists($uniFile)) {
            $this->command?->error("Missing file: {$uniFile}");
            return;
        }
        if (!File::exists($colFile)) {
            $this->command?->error("Missing file: {$colFile}");
            return;
        }

        $readJson = function (string $path): array {
            $raw = File::get($path);
            $decoded = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException("Invalid JSON in {$path}: " . json_last_error_msg());
            }
            return $decoded ?? [];
        };

        // Unwrap common wrapper keys and "single array child" wrappers.
        $unwrap = function ($v) {
            if (!is_array($v)) return $v;

            foreach ([
                'data',
                'all_universities',
                'universities',
                'universities_by_section',
                'colleges',
                'records',
                'results',
                'items',
                'rows',
                'list',
            ] as $k) {
                if (isset($v[$k]) && is_array($v[$k])) return $v[$k];
            }

            // If associative and has exactly one array child, unwrap it.
            if (array_keys($v) !== range(0, count($v) - 1)) {
                $arrayChildren = [];
                foreach ($v as $child) {
                    if (is_array($child)) $arrayChildren[] = $child;
                }
                if (count($arrayChildren) === 1) return $arrayChildren[0];
            }

            return $v;
        };

        $universitiesRaw = $readJson($uniFile);
        $collegesRaw     = $readJson($colFile);

        $universities = $unwrap($universitiesRaw);
        $colleges     = $unwrap($collegesRaw);

        // If universities are grouped by section, flatten them into a single list.
        if (is_array($universities) && array_keys($universities) !== range(0, count($universities) - 1)) {
            $flat = [];
            foreach ($universities as $group) {
                if (is_array($group)) {
                    foreach ($group as $u) $flat[] = $u;
                }
            }
            if (!empty($flat)) {
                $universities = $flat;
            }
        }

        // Helpful diagnostics (so you can quickly see shape mismatches)
        if (empty($universities)) {
            $topKeys = is_array($universitiesRaw) ? array_keys($universitiesRaw) : [];
            $this->command?->warn(
                'Universities list appears empty after unwrapping. Top-level keys: ' . implode(',', array_slice($topKeys, 0, 30))
            );
        }

        $now = now();

        $institutionsTable = 'education_institutions';

        $beforeUni = DB::table($institutionsTable)->where('type', 'University')->count();
        $beforeCol = DB::table($institutionsTable)->where('type', 'College')->count();

        $rows = [];

        // 1) Universities
        $uniCountJson = 0;
        foreach ((array) $universities as $u) {
            if (!is_array($u)) continue;

            $name = $u['name'] ?? $u['university_name'] ?? $u['University'] ?? null;
            if (!$name) continue;

            $district = $u['district'] ?? $u['location'] ?? $u['District'] ?? null;

            $rows[] = [
                'name'        => trim((string) $name),
                'type'        => 'University',
                'district'    => $district ? trim((string) $district) : null,
                'is_active'   => 1,
                'is_featured' => 0,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
            $uniCountJson++;
        }

        // 2) Colleges — unique by name, first 500 alphabetically as featured (deterministic)
        $collegeMap = [];
        foreach ((array) $colleges as $c) {
            if (!is_array($c)) continue;

            $name = $c['college_name'] ?? $c['name'] ?? $c['College Name'] ?? null;
            if (!$name) continue;

            $district = $c['district'] ?? $c['District'] ?? null;

            $key = mb_strtolower(trim((string) $name));
            if ($key === '') continue;

            if (!isset($collegeMap[$key])) {
                $collegeMap[$key] = [
                    'name' => trim((string) $name),
                    'district' => $district ? trim((string) $district) : null,
                ];
            }
        }

        $collegeList = array_values($collegeMap);
        usort($collegeList, fn ($a, $b) => strcmp($a['name'], $b['name']));

        $featuredLimit = 500;
        $featuredFlagged = min($featuredLimit, count($collegeList));

        $i = 0;
        foreach ($collegeList as $col) {
            $rows[] = [
                'name'        => $col['name'],
                'type'        => 'College',
                'district'    => $col['district'],
                'is_active'   => 1,
                'is_featured' => ($i < $featuredLimit) ? 1 : 0,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
            $i++;
        }

        // Insert (insertOrIgnore requires a UNIQUE index to truly de-dupe; still safe without it)
        foreach (array_chunk($rows, 1000) as $chunk) {
            DB::table($institutionsTable)->insertOrIgnore($chunk);
        }

        $afterUni = DB::table($institutionsTable)->where('type', 'University')->count();
        $afterCol = DB::table($institutionsTable)->where('type', 'College')->count();

        $this->command?->info('Universities in JSON: ' . $uniCountJson);
        $this->command?->info('Colleges in JSON (unique by name): ' . count($collegeList) . ' (Featured flagged: ' . $featuredFlagged . ')');

        $this->command?->info('Inserted Universities (actual): ' . max(0, $afterUni - $beforeUni));
        $this->command?->info('Inserted Colleges (actual): ' . max(0, $afterCol - $beforeCol));
    }
}
