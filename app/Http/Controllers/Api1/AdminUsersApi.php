<?php
namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Services\UsersService;
use App\Services\CompaniesService;

class AdminUsersApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\UsersService
     */
    protected $users;

    /**
     * @var App\Services\CompaniesService
     */
    protected $companies;

    /**
     * Constructor
     */
    public function __construct(Request $request, UsersService $users, CompaniesService $companies)
    {
        $this->request = $request;
        $this->users = $users;
        $this->companies = $companies;
    }

    /**
     * Operation createUser
     *
     * Create User.
     *
     *
     * @return Http response
     */
    public function createUser()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'company_id' => 'bail|required|integer|min:1',
            'username' => 'required|max:150',
            'password' => 'required|max:255',
            'confirm_password' => 'required|max:255',
            'email' => 'required|email|max:255',
            'fullname' => 'nullable|max:100',
            'birthday' => 'nullable',
            'address' => 'nullable|max:255',
            'sidn' => 'nullable|max:30',
            'gender' => 'nullable',
            'phone' => 'nullable|max:20',
        ]);

        // save user
        $input = $this->request->all();

        // check company exist
        if (!$this->companies->checkExistsById($input['company_id']))
            return response('Company Not found', 404);

        // get role manager
        $role = Role::where('name', 'manager')->first();
        $input['role_id'] = $role->id;

        return $this->users->createUser($input);
    }
    /**
     * Operation listUsers
     *
     * List of Users.
     *
     *
     * @return Http response
     */
    public function listUsers()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // get role manager
        $role = Role::where('name', 'manager')->first();

        if (!$role)
            return [];

        $input = $this->request->all();
        $input['role'] = 'admin';
        $input['role_id'] = $role->id;

        return $this->users->getListUser($input);

    }
    /**
     * Operation updateUser
     *
     * Updates a User.
     *
     *
     * @return Http response
     */
    public function updateUser()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'id' => 'bail|required|integer|min:1',
            'role_id' => 'required|integer|min:1',
            'company_id' => 'nullable',
            'email' => 'nullable|email|max:255',
            'fullname' => 'nullable|max:100',
            'birthday' => 'nullable',
            'address' => 'nullable|max:255',
            'sidn' => 'nullable|max:30',
            'gender' => 'nullable',
            'phone' => 'nullable|max:20',
        ]);

        // save user
        $input = $this->request->all();

        return $this->users->changeInforUser($input);
    }
    /**
     * Operation deleteUser
     *
     * Delete a User.
     *
     * @param int $user_id  (required)
     *
     * @return Http response
     */
    public function deleteUser($user_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return $this->users->deleteUser($user_id);
    }
    /**
     * Operation getUser
     *
     * get User by id.
     *
     * @param int $user_id  (required)
     *
     * @return Http response
     */
    public function getUser($user_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($user_id) || (int)$user_id < 0)
            return response('Invalid ID supplied', 404);

        $user = $this->users->getUserByKey('id', $user_id);

        if (empty($user)) {
            return response('User not found', 404);
        }

        return $user;
    }
    /**
     * Operation changePasswordOfUser
     *
     * change password of User.
     *
     * @param int $user_id ID of user that needs to be user (required)
     *
     * @return Http response
     */
    public function changePasswordOfUser($user_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'current_password' => 'required|max:255',
            'new_password' => 'required|max:255',
            'confirm_password' => 'required|max:255'
        ]);

        if (empty($user_id) || (int)$user_id < 0)
            return response('Invalid ID supplied', 404);

        // get User
        $input = $this->request->all();

        return $this->users->changePassword($user_id, $input);
    }

    /**
     * Operation listAllUser
     *
     * List all Users.
     *
     *
     * @return Http response
     */
    public function listAllUser()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['role'] = 'admin';
        $input['role_id'] = $role->id;

        return $this->users->getAllUser($input);
    }
}
