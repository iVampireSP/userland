<?php

namespace App\Jobs;

use App\Models\Package;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RevokeUserPackagePermissionsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $userId,
        protected int $packageId
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::find($this->userId);

        // 待吊销的 Package ID
        $revoke_package = Package::with(['roles', 'permissions'])->find($this->packageId);

        if (! $user || ! $revoke_package) {
            return;
        }

        $all_roles = [];
        $all_permissions = [];

        // 获取用户的所有角色
        $roles = $user->roles;

        $permissions = $user->getAllPermissions();

        foreach ($roles as $role) {
            if (! isset($all_roles[$role->name])) {
                // 如果没有 count, 则设置 count 为 1
                if (! isset($all_roles[$role->name]['count'])) {
                    $all_roles[$role->name]['count'] = 1;
                }
            } else {
                $all_roles[$role->name]['count'] += 1;
            }
        }

        foreach ($permissions as $permission) {
            if (! isset($all_permissions[$permission->name])) {
                // 如果没有 count, 则设置 count 为 1
                if (! isset($all_permissions[$permission->name]['count'])) {
                    $all_permissions[$permission->name]['count'] = 1;
                }
            } else {
                $all_permissions[$permission->name]['count'] += 1;
            }
        }

        // 获取用户的 package status = active
        $packages = $user->packages()->with('package.roles', 'package.permissions')->where('status', 'active')->get();
        foreach ($packages as $package) {
            foreach ($package->package->roles as $role) {
                if (! isset($all_roles[$role->name])) {
                    // 如果没有 count, 则设置 count 为 1
                    if (! isset($all_roles[$role->name]['count'])) {
                        $all_roles[$role->name]['count'] = 1;
                    }
                } else {
                    $all_roles[$role->name]['count'] += 1;
                }
            }
            foreach ($package->package->permissions as $permission) {
                if (! isset($all_permissions[$permission->name])) {
                    // 如果没有 count, 则设置 count 为 1
                    if (! isset($all_permissions[$permission->name]['count'])) {
                        $all_permissions[$permission->name]['count'] = 1;
                    }
                } else {
                    $all_permissions[$permission->name]['count'] += 1;
                }
            }
        }

        // 获取待吊销的 $package
        $package_roles = $revoke_package->roles ?? [];
        $package_permissions = $revoke_package->getAllPermissions() ?? [];
        foreach ($package_roles as $role) {
            if (isset($all_roles[$role->name])) {
                $all_roles[$role->name]['count'] -= 1;
            }
        }
        foreach ($package_permissions as $permission) {
            if (isset($all_permissions[$permission->name])) {
                $all_permissions[$permission->name]['count'] -= 1;
            }
        }

        // 获取所有 count 为 0 的
        $roles_to_revoke = array_filter($all_roles, function ($value) {
            return $value['count'] == 0;
        });

        $permissions_to_revoke = array_filter($all_permissions, function ($value) {
            return $value['count'] == 0;
        });

        foreach ($roles_to_revoke as $role_id => $value) {
            $user->removeRole($role_id);
        }

        foreach ($permissions_to_revoke as $permission_id => $value) {
            $user->revokePermissionTo($permission_id);
        }

    }
}
