<?php

namespace App\Jobs;

use App\Models\Package;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GrantPackagePermissionsToUserJob implements ShouldQueue
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
        $package = Package::with(['roles', 'permissions'])->find($this->packageId);

        if (! $user || ! $package) {
            return;
        }

        $package_roles = $package->roles->pluck('name');
        $package_permissions = $package->getAllPermissions()->pluck('name');

        foreach ($package_roles as $role_name) {
            $user->assignRole($role_name);
        }

        foreach ($package_permissions as $permission_name) {
            $user->givePermissionTo($permission_name);
        }

    }
}
