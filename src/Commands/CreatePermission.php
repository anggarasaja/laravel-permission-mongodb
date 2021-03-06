<?php
// declare(strict_types=1);

namespace Anggarasaja\Permission\Commands;

use Illuminate\Console\Command;
use Anggarasaja\Permission\Contracts\PermissionInterface as Permission;

/**
 * Class CreatePermission
 * @package Anggarasaja\Permission\Commands
 */
class CreatePermission extends Command
{
    protected $signature = 'permission:create-permission 
                {name : The name of the permission} 
                {guard? : The name of the guard}';

    protected $description = 'Create a permission';

    public function handle()
    {
        $permissionClass = \app(Permission::class);

        $permission = $permissionClass::create([
            'name'       => $this->argument('name'),
            'guard_name' => $this->argument('guard')
        ]);

        $this->info("Permission `{$permission->name}` created");
    }
}
