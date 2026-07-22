<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ModuleController extends Controller
{
    public function index()
    {
        abort_if(! userCan('setting.view'), 403);

        $modulesPath = base_path('modules_statuses.json');
        $modules = [];

        if (File::exists($modulesPath)) {
            $modules = json_decode(File::get($modulesPath), true);
        }

        return view('backend.modules.index', compact('modules'));
    }

    public function update(Request $request)
    {
        abort_if(! userCan('setting.view'), 403);

        $request->validate([
            'modules' => 'array',
            'modules.*' => 'boolean',
        ]);

        $modulesPath = base_path('modules_statuses.json');
        $currentModules = [];

        if (File::exists($modulesPath)) {
            $currentModules = json_decode(File::get($modulesPath), true);
        }

        // Track which modules are being enabled for the first time
        $newlyEnabledModules = [];

        // Update module statuses
        $updatedModules = [];
        foreach ($currentModules as $module => $status) {
            $newStatus = isset($request->modules[$module]) ? true : false;
            $updatedModules[$module] = $newStatus;

            // Check if module is being enabled for the first time
            if ($newStatus && ! $status) {
                $newlyEnabledModules[] = $module;
            }
        }

        // Save to file
        File::put($modulesPath, json_encode($updatedModules, JSON_PRETTY_PRINT));

        // Run migrations for newly enabled modules
        if (! empty($newlyEnabledModules)) {
            $migrationMessages = [];
            foreach ($newlyEnabledModules as $module) {
                $result = $this->runModuleMigrations($module);
                if ($result) {
                    $migrationMessages[] = $module;
                }
            }

            if (! empty($migrationMessages)) {
                flashSuccess(__('modules_updated_successfully').' - Migrations completed for: '.implode(', ', $migrationMessages));
            } else {
                flashSuccess(__('modules_updated_successfully'));
            }
        } else {
            flashSuccess(__('modules_updated_successfully'));
        }

        // Clear cache
        Artisan::call('cache:clear');
        Artisan::call('config:clear');

        return redirect()->route('modules.index');
    }

    private function runModuleMigrations($moduleName)
    {
        try {
            // Check if module directory exists
            $modulePath = base_path("Modules/{$moduleName}");
            if (! File::exists($modulePath)) {
                return false; // Indicate failure if module directory not found
            }

            // Check if module has migrations
            $migrationsPath = "{$modulePath}/Database/Migrations";
            if (! File::exists($migrationsPath)) {
                return false; // Indicate failure if migrations directory not found
            }

            // Get all migration files
            $migrationFiles = File::glob("{$migrationsPath}/*.php");

            if (empty($migrationFiles)) {
                return false; // Indicate failure if no migrations found
            }

            // Run migrations for this module
            Artisan::call('migrate', [
                '--path' => "Modules/{$moduleName}/Database/Migrations",
                '--force' => true,
            ]);

            // Log the migration
            Log::info("Module {$moduleName} migrations completed successfully");

            return true; // Indicate success

        } catch (\Exception $e) {
            Log::error("Error running migrations for module {$moduleName}: ".$e->getMessage());

            return false; // Indicate failure
        }
    }
}
