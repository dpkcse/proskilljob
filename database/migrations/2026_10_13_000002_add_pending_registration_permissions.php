<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superadmin = Role::firstOrCreate([
            'name' => 'superadmin',
            'guard_name' => 'admin',
        ]);

        foreach (['view', 'resend', 'delete'] as $action) {
            $permission = Permission::firstOrCreate(
                [
                    'name' => "pending-registration.{$action}",
                    'guard_name' => 'admin',
                ],
                ['group_name' => 'pending-registration']
            );

            $superadmin->givePermissionTo($permission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::query()
            ->where('guard_name', 'admin')
            ->whereIn('name', [
                'pending-registration.view',
                'pending-registration.resend',
                'pending-registration.delete',
            ])
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
