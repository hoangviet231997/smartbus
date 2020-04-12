<?php
namespace App\Services;

use App\Models\Permission;

class PermissionsService
{
    public function __construct()
    {

    }

    public function checkExistsByKey($key)
    {
        return Permission::where('key', $key)
                    ->exists();
    }

    public function getPermissionByKey($key)
    {
        return Permission::where('key', $key)
                    ->first();
    }
}