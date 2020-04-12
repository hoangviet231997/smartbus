<?php
namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\PermissionRole;
use App\Models\Permission;

class AdminRolesApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * Constructor
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Operation createRole
     *
     * create.
     *
     *
     * @return Http response
     */
    public function createRole()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'name' => 'required',
            'display_name' => 'required'
        ]);

        // save Role
        $input = $this->request->all();
        //check exist
        $check = Role::where('name', '=', $input['name'])->first();
        
        if ($check) {
            return response('Role exist.', 404);
        }

        $role = new Role;
        $role->name = $input['name'];
        $role->display_name = $input['display_name'];

        if ($role->save())
            return $this->getRoleById($role['id']);

        return response('Create Error', 404);
    }

    /**
     * Operation listRoles
     *
     * list.
     *
     *
     * @return Http response
     */
    public function listRoles()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return Role::all()->toArray();
    }

    /**
     * Operation updateRole
     *
     * update.
     *
     *
     * @return Http response
     */
    public function updateRole()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'id' => 'bail|required|integer|min:1',
            'display_name' => 'required'
        ]);

        // save Role
        $input = $this->request->all();
        
        $role = Role::find($input['id']);

        if (empty($role)) return response('Role Not found', 404);

        $role->display_name = $input['display_name'];

        if ($role->save()) return $this->getRoleById($role->id);
        
        return response('Update Error', 404);        
    }

    /**
     * Operation deleteRole
     *
     * delete.
     *
     * @param int $role_id  (required)
     *
     * @return Http response
     */
    public function deleteRole($role_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($role_id) || (int)$role_id < 0) 
            return response('Invalid ID supplied', 404);

        // get Role
        $role = Role::find($role_id);

        if (empty($role)) return response('Role Not found', 404);

        if ($role->delete()) return response('OK', 200);
        
        return response('Delete Error', 404);  
    }

    /**
     * Operation getRoleById
     *
     * Find by ID.
     *
     * @param int $role_id  (required)
     *
     * @return Http response
     */
    public function getRoleById($role_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($role_id) || (int)$role_id < 0) 
            return response('Invalid ID supplied', 404);

        // get Role
        $role = Role::find($role_id);

        if (empty($role)) return response('Role Not found', 404);

        return $role;
    }

    /**
     * Operation assignPermissionToRoleId
     *
     * assign Permission To Role.
     *
     * @param int $role_id  (required)
     *
     * @return Http response
     */
    public function assignPermissionToRoleId($role_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($role_id) || (int)$role_id < 0) 
            return response('Invalid ID supplied', 404);

        // check role exist
        if (!Role::where('id', $role_id)->exists())
            return response('Role Not found', 404);

        // remove all 
        $permissions = PermissionRole::where('role_id', $role_id)->get();

        if (count($permissions) > 0) {

            foreach ($permissions as $permission) {
     
                $remove = PermissionRole::where('permission_id', $permission->permission_id)
                            ->where('role_id', $role_id);
                $remove->delete();
            }
        }

        // save 
        $input = $this->request->all();
        $permissions = $input;

        if (!empty($permissions) && count($permissions) > 0) {

            foreach ($permissions as $per) {
                $permissionRole = new PermissionRole();
                $permissionRole->permission_id = $per;
                $permissionRole->role_id = $role_id;
                $permissionRole->save();
            }
        }

        return response('OK', 200);
    }

    /**
     * Operation getPermissionsByRoleId
     *
     * Find by ID.
     *
     * @param int $role_id  (required)
     *
     * @return Http response
     */
    public function getPermissionsByRoleId($role_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($role_id) || (int)$role_id < 0) 
            return response('Invalid ID supplied', 404);

        // check role exist
        if (!Role::where('id', $role_id)->exists())
            return response('Role Not found', 404);

        $permissions = PermissionRole::where('role_id', $role_id)->get();  
        $permissions_arr = [];

        if (count($permissions) > 0) {
            
            foreach ($permissions as $perm) {
                $permission = Permission::find($perm->permission_id);

                if (!empty($permission)) array_push($permissions_arr, $permission);
            }
        }

        return $permissions_arr; 
    }
    
    /**
     * Operation listPermissionRoles
     *
     * list.
     *
     *
     * @return Http response
     */
    public function listPermissionRoles()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return Role::where('name', '!=', 'admin')->get();
    }  
}