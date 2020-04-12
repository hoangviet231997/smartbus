<?php

namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Api1\ApiController;
use Illuminate\Contracts\Auth\Factory as Auth;
use JWTAuth;
use App\Models\User;
use App\Models\PermissionRole;
use App\Models\Permission;
use App\Models\Company;
use App\Models\Session;

use Illuminate\Http\Request;

class ManagerLayoutApi extends ApiController
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

    public function listPermissionsByRoleAndCompanyId()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //check disable 
        if ($user->disable == 1) {
            return response()->json(['user_not_found'], 404);
        }

        $permissions = PermissionRole::join('permissions', 'permissions.id', '=', 'permission_roles.permission_id')
            ->join('roles', 'roles.id', '=', 'permission_roles.role_id')
            ->where([
                ['permission_roles.role_id', '=', $user->role_id],
                ['permission_roles.company_id', '=', $user->company_id],
                ['permission_roles.company_id', '!=', null],
                ['permission_roles.deleted_at', '=', null],
            ])
            ->select('permissions.*', 'roles.name as roles_name', 'permission_roles.company_id')
            ->get();
            
        $result = [];
        if (count($permissions) > 0) {
            foreach ($permissions as $permission) {
                // check role
                $accepts = ['admin', 'manager', 'teller', 'collecter', 'staff', 'accountant'];
                if (!in_array($permission->roles_name, $accepts)) {
                    return response()->json(['user_not_found'], 404);
                }
                $result[$permission->key] = $permission;
            }
        }

        return $result;
    }
}
