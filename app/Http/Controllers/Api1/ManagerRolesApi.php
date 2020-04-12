<?php
namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Models\Role;


class ManagerRolesApi extends ApiController
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
     * Operation managerListRoles
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerListRoles()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return Role::where('name', '!=', 'admin')
                ->where('name', '!=', 'manager')->get();
    }  
}