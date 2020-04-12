<?php
namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\PermissionRole;

class AdminPermissionsApi extends ApiController
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
     * Operation createPermission
     *
     * create.
     *
     *
     * @return Http response
     */
    public function createPermission()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'key' => 'required'
        ]);

        // save Permission
        $input = $this->request->all();

        //check exist
        $check = Permission::where('permissions.key', '=', $input['key'])
                            ->join('permission_roles', 'permissions.id', '=', 'permission_roles.permission_id')
                            ->where('permission_roles.role_id', '=', $input['role_id'])
                            ->where('permission_roles.company_id',$input['company_id'])
                            ->first();
        if ($check) return response('This permission already exists', 404);
        
        $permission = new Permission;
        $permission->key = $input['key'];
        $permission->edit = 0;
        $permission->key_tools = $input['key_tools'] ?? null;

        if ($permission->save())
            //insert PermissionRole
            $permissionRole = new PermissionRole();
            $permissionRole->permission_id = $permission->id;
            $permissionRole->role_id = $input['role_id'];
            $permissionRole->company_id = $input['company_id'];
            $permissionRole->save();
            return $this->getPermissionById($permission['id']);

        return response('Create Error', 404);        
    }

    /**
     * Operation listPermissions
     *
     * list.
     *
     *
     * @return Http response
     */
    public function listPermissions()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $limit = $input['limit'];

        if (empty($limit) && $limit < 0)
            $limit = 10;

        $pagination = Permission::join('permission_roles', 'permissions.id', '=', 'permission_roles.permission_id')
            ->join('roles', 'roles.id', '=', 'permission_roles.role_id')
            ->join('companies', 'companies.id', '=', 'permission_roles.company_id')
            ->select('permissions.*','permission_roles.company_id','companies.name as company_name', 'roles.name as role_name')
            ->orderBy('created_at', 'desc')
            ->paginate($limit)
            ->toArray();

        header("pagination-total: " . $pagination['total']);
        header("pagination-current: " . $pagination['current_page']);
        header("pagination-last: " . $pagination['last_page']);

        return $pagination;
    }

    /**
     * Operation updatePermission
     *
     * update.
     *
     *
     * @return Http response
     */
    public function updatePermission()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'id' => 'bail|required|integer|min:1',
            'key' => 'required'
        ]);

        // save Permission
        $input = $this->request->all();

        $permission = Permission::find($input['id']);

        if (empty($permission)) 
            return response('Permission Not found', 404);

        //check exist
        $check = Permission::where('permissions.key', '=', $input['key'])
                            ->join('permission_roles', 'permissions.id', '=', 'permission_roles.permission_id')
                            ->where('permission_roles.role_id', '=', $input['role_id'])
                            ->where('permission_roles.company_id',$input['company_id'])
                            ->where('permissions.id', '!=', $input['id'])
                            ->first();
        if ($check) {
            return response('This permission already exists', 404);
        }

        $permission->key = $input['key'];
        $permission->edit = 0;
        $permission->key_tools = $input['key_tools'] ?? null;

        if ($permission->save()){
            //update PermissionRole
            $permissionRole = PermissionRole::where('permission_id', $permission->id)->first();
            $permissionRole->permission_id = $permission->id;
            $permissionRole->role_id = $input['role_id'];
            $permissionRole->company_id = $input['company_id'];
            $permissionRole->save();
            return $permission;
        }
           
        return response('Update Error', 404);
    }
    /**
     * Operation deletePermission
     *
     * delete.
     *
     * @param int $permission_id  (required)
     *
     * @return Http response
     */
    public function deletePermission($permission_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($permission_id) || (int)$permission_id < 0) 
            return response('Invalid ID supplied', 404);

        // get Permission
        $permission = Permission::find($permission_id);

        if (empty($permission)) return response('Permission Not found', 404);

        if ($permission->delete())

        // get Permission role
        $permissionRole = PermissionRole::where('permission_id', $permission->id);
        if ($permissionRole->delete()) return response('OK', 200);
        
        return response('Delete Error', 404);
    }
    /**
     * Operation getpermissionById
     *
     * Find by ID.
     *
     * @param int $permission_id  (required)
     *
     * @return Http response
     */
    public function getPermissionById($permission_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($permission_id) || (int)$permission_id < 0) 
            return response('Invalid ID supplied', 404);

        // get Role
        $permission = Permission::join('permission_roles', 'permissions.id', '=', 'permission_roles.permission_id')
                                ->join('roles', 'roles.id', '=', 'permission_roles.role_id')
                                ->join('companies','companies.id','=','permission_roles.company_id')
                                ->where('permissions.id', $permission_id)
                                ->select('permissions.*', 'permission_roles.role_id as role_id','permission_roles.company_id','companies.name as company_name', 'roles.name as role_name')
                                ->first();

        if (empty($permission)) return response('Permission Not found', 404);

        return $permission;        
    }   
    
    /**
     * Operation searchPermissions
     *
     * search.
     *
     *
     * @return Http response
     */
    public function searchPermissions()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        //get permission
        $permissions = Permission::join('permission_roles', 'permissions.id', '=', 'permission_roles.permission_id')
                                ->join('companies','companies.id','=','permission_roles.company_id')
                                ->join('roles', 'roles.id', '=', 'permission_roles.role_id');
                            
        if ($input['role_id'] > 0) {
            $permissions->where('permission_roles.role_id', '=', $input['role_id']);
        }

        if ($input['company_id'] > 0) {
            $permissions->where('permission_roles.company_id',$input['company_id']);
        }

        if (!$permissions) {
            return response('Permission Not found', 404);
        }

        return $permissions->select('permissions.*','permission_roles.company_id','companies.name as company_name', 'roles.name as role_name')->get();        
    }
    
}