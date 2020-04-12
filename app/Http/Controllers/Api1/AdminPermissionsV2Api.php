<?php

namespace App\Http\Controllers\Api1;

use App\Models\Permission;
use App\Models\PermissionRole;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPermissionsV2Api extends ApiController
{
    /**
     * Constructor
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    // public function listPermissionsV2()
    // {
    //     // check login
    //     $user = $this->requiredAuthUser();
    //     if (empty($user)) return response('token_invalid', 401);

    //     return Permission::join('permission_roles', 'permissions.id', '=', 'permission_roles.permission_id')
    //         ->join('categories', 'categories.key', '=', 'permissions.key')
    //         ->join('companies', 'permission_roles.company_id', '=', 'companies.id')
    //         ->join('roles', 'permission_roles.role_id', '=', 'roles.id')
    //         ->select('permissions.*', 'categories.display_name as page_name', 'companies.name as company_name', 'roles.name as role_name')
    //         ->get();
    // }

    public function getPermissionV2ByRoleIdAndCompanyId($role_id, $company_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($role_id) || (int) $role_id < 0)
            return response('Invalid ID supplied', 404);
        // check id
        if (empty($company_id) || (int) $company_id < 0)
            return response('Invalid ID supplied', 404);

        $permission_roles =  Permission::join('permission_roles', 'permissions.id', '=', 'permission_roles.permission_id')
            ->join('companies', 'companies.id', '=', 'permission_roles.company_id')
            ->join('roles', 'roles.id', '=', 'permission_roles.role_id')
            ->join('categories', 'categories.key', '=', 'permissions.key')
            ->where('permission_roles.company_id', $company_id)
            ->where('permission_roles.role_id', $role_id)
            ->select(
                'permissions.*', 
                'permission_roles.company_id', 
                'companies.name as company_name', 
                'roles.name as role_name', 
                'roles.id as role_id', 
                'categories.display_name as page_name'
                )
            ->get();
        
        $result = [
            "role_name" => '',
            "role_id" => $role_id,
            "company_id" => $company_id,
            "permission_data" => []
        ];
        if(count($permission_roles) > 0){

            $result['role_name'] = $permission_roles[0]['role_name'];

            foreach ($permission_roles as  $v) {
                $v['key_tools'] = ( $v['key_tools'] != null) ? json_decode( $v['key_tools'], true) : [];
                $result['permission_data'][] = $v;
            }

            $result['permission_data'] = json_encode($result['permission_data']);
        }

        return $result;
    }

    public function createPermissionV2()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'company_id' => 'required',
            'role_id' => 'required',
            'permission_data' => 'required'
        ]);

        // save Permission
        $input = $this->request->all();
        $company_id = $input['company_id'];
        $role_id = $input['role_id'];
        $permission_data = json_decode($input['permission_data'], true) ?? [];

        //check exist
        $check_role_exist = Permission::join('permission_roles', 'permissions.id', '=', 'permission_roles.permission_id')
            ->where('permission_roles.role_id', '=', $role_id)
            ->where('permission_roles.company_id', $company_id)
            ->exists();

        if ($check_role_exist) {
            return response('Role permission exist', 404);
        }

        $result = new \stdClass;
        $result->role_id = $role_id;
        $result->company_id = $company_id;
        $result->permission_data = [];

        if (count($permission_data) > 0) {

            foreach ($permission_data as $values) {

                if ((int)$values['id'] > 0) {

                    //check exist
                    $check = Permission::where('permissions.key', '=', $values['key'])
                        ->join('permission_roles', 'permissions.id', '=', 'permission_roles.permission_id')
                        ->join('categories', 'categories.key', '=', 'permissions.key')
                        ->where('permission_roles.role_id', '=', $role_id)
                        ->where('permission_roles.company_id', $company_id)
                        ->select('permissions.*', 'categories.display_name as page_name')
                        ->first();

                    if ($check) continue;

                    $permission = new Permission;
                    $permission->key = $values['key'];
                    $permission->edit = 0;
                    $permission->key_tools = (count($values['key_tools']) > 0) ? json_encode($values['key_tools']) : null;

                    if ($permission->save()) {
                        //insert PermissionRole
                        $permissionRole = new PermissionRole();
                        $permissionRole->permission_id = $permission->id;
                        $permissionRole->role_id = $role_id;
                        $permissionRole->company_id = $company_id;
                        $permissionRole->save();

                        $result->permission_data[] = [
                            'key' => $permission->key,
                            'key_tools' => $values['key_tools']
                        ];
                    }
                }
            }
            if (count($result->permission_data) > 0) {
                $result->permission_data = json_encode($result->permission_data);
                return [$result];
            }
        }
        return response('Create Error', 404);
    }

    public function updatePermissionV2()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'company_id' => 'required',
            'role_id' => 'required',
            'permission_data' => 'required'
        ]);

        // save Permission
        $input = $this->request->all();
        $company_id = (int)$input['company_id'];
        $role_id = (int)$input['role_id'];
        $permission_data = json_decode($input['permission_data'], true) ?? [];

        //check exist

        $result = [
            "role_name" => '',
            "role_id" => $role_id,
            "company_id" => $company_id,
            "permission_data" => []
        ];

        if(count($permission_data) > 0){

            foreach ($permission_data as $values) {

                //$values['status'] == 0  => 'old' 
                //$values['status'] == 1  => 'new' 

                $values = (array)$values;

                if($values['status'] == 0){

                    if((int)$values['id'] > 0) {

                        $permission = Permission::where('permissions.id', (int)$values['id'])->first();
                        if(empty($permission)) return response('Permission not found', 404);

                        $permission->key = $values['key'];
                        $permission->edit = 0;
                        $permission->key_tools = (count($values['key_tools']) > 0) ? json_encode($values['key_tools']) : null;
        
                        if ($permission->save()) {
                            $result['permission_data'][] = [
                                'key' => $permission->key,
                                'key_tools' => $values['key_tools']
                            ];
                        }
                    }
                    if((int)$values['id'] < 0){
                        // get Permission role
                        $permission_role = PermissionRole::where('permission_id', ((int)$values['id']*(-1)))->delete();
                        $permission = Permission::where('id', ((int)$values['id']*(-1)))->delete();
                    }
                }

                if($values['status'] == 1){

                    if ($values['id'] > 0) {

                        $check = Permission::where('permissions.key', '=', $values['key'])
                            ->join('permission_roles', 'permissions.id', '=', 'permission_roles.permission_id')
                            ->join('categories', 'categories.key', '=', 'permissions.key')
                            ->where('permission_roles.role_id', '=', $role_id)
                            ->where('permission_roles.company_id', $company_id)
                            ->first();
        
                        if ($check) continue;

                        $permission = new Permission;
                        $permission->key = $values['key'];
                        $permission->edit = 0;
                        $permission->key_tools = (count($values['key_tools']) > 0) ? json_encode($values['key_tools']) : null;
        
                        if ($permission->save()) {
                            //insert PermissionRole
                            $permissionRole = new PermissionRole();
                            $permissionRole->permission_id = $permission->id;
                            $permissionRole->role_id = $role_id;
                            $permissionRole->company_id = $company_id;
                            $permissionRole->save();
    
                            $result['permission_data'][] = [
                                'key' => $permission->key,
                                'key_tools' => $values['key_tools']
                            ];
                        }
                    }
                }
            }
        }

        $result['permission_data'] = json_encode($result['permission_data']);
      
        return $result;
    }

    public function deletePermissionV2ByRoleIdAndCompanyId($role_id, $company_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($role_id) || (int) $role_id < 0)
            return response('Invalid ID supplied', 404);

        // check id
        if (empty($company_id) || (int) $company_id < 0)
            return response('Invalid ID supplied', 404);

        // get Permission
        $permissions = Permission::join('permission_roles', 'permissions.id', '=', 'permission_roles.permission_id')
                        ->where('permission_roles.role_id', '=', $role_id)
                        ->where('permission_roles.company_id', $company_id)
                        ->select('permissions.*')
                        ->get()->toArray();
        if(count($permissions) > 0){
            foreach ($permissions as  $value) {
                $permission_role = PermissionRole::where('permission_id', $value['id'])->delete();
                $permission = Permission::where('id', $value['id'])->delete();
            }
            return response('OK', 200);
        }
        return response('Permission Not found', 404);       
    }

    public function searchPermissionsV2()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $result = [];

        $permission_roles =  Permission::join('permission_roles', 'permissions.id', '=', 'permission_roles.permission_id')
            ->join('companies', 'companies.id', '=', 'permission_roles.company_id')
            ->join('roles', 'roles.id', '=', 'permission_roles.role_id')
            ->join('categories', 'categories.key', '=', 'permissions.key')
            ->where('permission_roles.company_id', $input['company_id'])
            ->select(
                'permissions.*', 
                'permission_roles.company_id', 
                'companies.name as company_name', 
                'roles.name as role_name', 
                'roles.id as role_id', 
                'categories.display_name as page_name'
                )
            ->get();
           

        if(count($permission_roles) > 0){
            
            $permissions = collect($permission_roles)->groupBy('role_id')->toArray();
            foreach ($permissions as $values) {
                $tmp = new \stdClass;
                $tmp->role_name = $values[0]['role_name'];
                $tmp->role_id = $values[0]['role_id'];
                $tmp->company_id = $values[0]['company_id'];
                $tmp->permission_data = [];
                foreach ($values as  $v) {
                    $v['key_tools'] = ( $v['key_tools'] != null) ? json_decode( $v['key_tools'], true) : [];
                    $tmp->permission_data[] = $v;
                }
                $result[] = $tmp;
            }
        }

        return $result;
    }
}
