<?php
namespace App\Services;

use App\Models\Role;
use App\Models\Permission;
use App\Services\PermissionsService;
use App\Services\PermissionRolesService;

class RolesService
{
    /**
     * @var App\Services\DevicesService
     */
    protected $permissions;

    /**
     * @var App\Services\DevicesService
     */
    protected $permission_roles;    

    public function __construct(PermissionsService $permissions, PermissionRolesService $permission_roles)
    {
        $this->permissions = $permissions;
        $this->permission_roles = $permission_roles;
    }

    public function isAuthorized($role_id, $key)
    {
        // check key and get get permission by key
        $checkPermission = $this->permissions->checkExistsByKey($key);

        if (!$checkPermission) return false;
        
        $permission = $this->permissions->getPermissionByKey($key);

        if (empty($permission)) return false;

        // check permission roles
        $is_valid = $this->permission_roles->checkExistsByRoleAndPermission(
                        $role_id, $permission->id);
        
        return $is_valid;
    }

    public function getRoleById($id)
    {
        return Role::find($id);
    }

    public function getRoleByKey($key, $value)
    {
        return Role::where($key, $value)->first();
    }  
    
    public function getRoleByKeys($value)
    {
        return Role::whereIn('name', $value)->get();
    }

    public function getIdRolePluckByName($name){
        return Role::whereIn('name', $name)->pluck('id')->toArray();
    }
}