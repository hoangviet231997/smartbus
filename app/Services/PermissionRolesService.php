<?php
namespace App\Services;

use App\Models\PermissionRole;

class PermissionRolesService
{
    public function __construct()
    {

    }

    public function checkExistsByRoleAndPermission($role_id, $permission_id)
    {
        return PermissionRole::where('role_id', $role_id)
                    ->where('permission_id', $permission_id)
                    ->exists();
    }
}