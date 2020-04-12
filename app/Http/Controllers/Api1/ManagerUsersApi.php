<?php
namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Services\RolesService;
use App\Services\RfidCardsService;
use App\Services\UsersService;

class ManagerUsersApi extends ApiController
{

    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\RolesService
     */
    protected $roles;

    /**
     * @var App\Services\RfidCardsService
     */
    protected $rfidcards;

    /**
     * @var App\Services\UsersService
     */
    protected $users;

    /**
     * Constructor
     */
    public function __construct(Request $request, RolesService $roles, RfidCardsService $rfidcards, UsersService $users)
    {
        $this->request = $request;
        $this->roles = $roles;
        $this->rfidcards = $rfidcards;
        $this->users = $users;
    }

    /**
     * Operation managerCreateUser
     *
     * Create User.
     *
     *
     * @return Http response
     */
    public function managerCreateUser()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $company_id = $user->company_id;

        // check is Authorized
        $role_id = $user->role_id;

        // if (!$this->roles->isAuthorized($role_id, 'ManagerUsers.create')) {
        //     return response('Permission denied', 404);
        // }

        //path params validation
        $this->validate($this->request, [
            'role_id' => 'required|integer|min:1',
            'username' => 'required|max:150',
            'password' => 'required|max:255',
            'confirm_password' => 'required|max:255',
            'email' => 'nullable|email|max:255',
            'fullname' => 'nullable|max:100',
            'birthday' => 'nullable',
            'address' => 'nullable|max:255',
            'sidn' => 'nullable|max:30',
            'gender' => 'nullable',
            'phone' => 'nullable|max:20'
        ]);

        // save user
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->users->createUser($input);
    }

    /**
     * Operation managerListUsers
     *
     * List of Users.
     *
     *
     * @return Http response
     */
    public function managerListUsers()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check is Authorized
        $role_id = $user->role_id;

        // if (!$this->roles->isAuthorized($role_id, 'ManagerUsers.access')) {
        //     return response('Permission denied', 404);
        // }

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;
        $input['user_id'] = $user->id;
        $input['role'] = 'manager';

        return $this->users->getListUser($input);
    }

    /**
     * Operation managerUpdateUser
     *
     * Updates a User.
     *
     *
     * @return Http response
     */
    public function managerUpdateUser()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check is Authorized
        $role_id = $user->role_id;

        // if (!$this->roles->isAuthorized($role_id, 'ManagerUsers.update')) {
        //     return response('Permission denied', 404);
        // }

        $company_id = $user->company_id;

        //path params validation
        $this->validate($this->request, [
            'id' => 'bail|required|integer|min:1',
            'role_id' => 'required|integer|min:1',
            'email' => 'nullable|email|max:255',
            'fullname' => 'nullable|max:100',
            'birthday' => 'nullable',
            'address' => 'nullable|max:255',
            'sidn' => 'nullable|max:30',
            'gender' => 'nullable',
            'phone' => 'nullable|max:20'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->users->updateUser($input);
    }

    /**
     * Operation managerDeleteUser
     *
     * Delete a User.
     *
     * @param int $user_id  (required)
     *
     * @return Http response
     */
    public function managerDeleteUser($user_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check is Authorized
        $role_id = $user->role_id;

        // if (!$this->roles->isAuthorized($role_id, 'ManagerUsers.delete')) {
        //     return response('Permission denied', 404);
        // }

        return $this->users->deleteUser($user_id, $user->company_id);
    }

     /**
     * Operation managerActionUser
     *
     * Updete a User.
     *
     * @param int $user_id  (required)
     *
     * @return Http response
     */
    public function managerActionUser()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check is Authorized
        $role_id = $user->role_id;
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->users->disableUser($input);
    }

    /**
     * Operation managerGetUser
     *
     * get User by id.
     *
     * @param int $user_id  (required)
     *
     * @return Http response
     */
    public function managerGetUser($user_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check is Authorized
        $role_id = $user->role_id;

        // if (!$this->roles->isAuthorized($role_id, 'ManagerUsers.get')) {
        //     return response('Permission denied', 404);
        // }

        $company_id = $user->company_id;

        // check id
        if (empty($user_id) || (int)$user_id < 0)
            return response('Invalid ID supplied', 404);

        $user = $this->users->getUserByKey('id', $user_id, $company_id);

        if (empty($user)) {
            return response('User not found', 404);
        }

        return $user;
    }

    /**
     * Operation managerSearchUser
     *
     * Search User.
     *
     *
     * @return Http response
     */
    public function managerSearchUser()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->users->searchUser($input);
    }
     /**
     * Operation managerListUserInput
     *
     * List of Users.
     *
     * @param string $key_word  (required)
     *
     * @return Http response
     */
    public function managerListUserInput()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;
        $input['user_id'] = $user->id;

        return $this->users->getListUserByInputName($input);
    }

    /**
     * Operation managerListAllUser
     *
     * List all Users.
     *
     *
     * @return Http response
     */
    public function managerListAllUser()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['role'] = 'manager';
        $input['company_id'] = $user->company_id;

        return $this->users->getAllUser($input);
    }
}
