<?php

namespace App\Services;

use App\Models\User;
use App\Models\PermissionRole;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use App\Services\RfidCardsService;
use App\Services\RolesService;
use App\Services\PushLogsService;
use Illuminate\Support\Facades\DB;

class UsersService
{
    /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;

    /**
     * @var App\Services\RfidCardsService
     */
    protected $rfidcards;

    /**
     * @var App\Services\RolesService
     */
    protected $roles;

    public function __construct(PushLogsService $push_logs, RfidCardsService $rfidcards, RolesService $roles)
    {
        $this->push_logs = $push_logs;
        $this->rfidcards = $rfidcards;
        $this->roles = $roles;
    }

    public function checkExistsByKey($key, $value)
    {
        return User::where($key, $value)->exists();
    }

    public function createUser($data)
    {
        $role_id = $data['role_id'];
        $company_id = $data['company_id'];
        $rfid = $data['rfid'] ?? null;
        $username = $data['username'];
        $password = $data['password'];
        $confirm_password = $data['confirm_password'];
        $email = $data['email'] ?? null;
        $fullname = $data['fullname'] ?? null;
        $birthday = $data['birthday'] ?? null;
        $address = $data['address'] ?? null;
        $sidn = $data['sidn'] ?? null;
        $gender = $data['gender'];
        $phone = $data['phone'] ?? null;
        $rfidcard_id = null;

        // check
        if ($password != $confirm_password) {
            return response('Password does not match the confirm password.', 404);
        }

        if ($this->checkExistsByKey('username', $username)) {
            return response('User already exists.', 404);
        }

        // check rfidcard
        $rfidcard = null;

        if (!empty($rfid)) {

            $rfidcard = $this->rfidcards->getRfidCardByRfid($rfid);

            if (empty($rfidcard)) {
                return response('Rfid card not found.', 404);
            }

            if ($this->rfidcards->checkRfidCardUsed($rfidcard->rfid, $rfidcard->barcode)) {
                return response('The rfid card has been used.', 404);
            }

            $rfidcard_id = $rfidcard->id;
        }

        // check role
        $role = $this->roles->getRoleById($role_id);

        if (empty($role)) {
            return response('Role not found.', 404);
        }

        $user = new User();
        $user->role_id = intval($role_id);
        $user->company_id = $company_id;
        $user->rfidcard_id = $rfidcard_id;
        $user->username = $username;
        $user->password = Hash::make($password);
        $user->email = $email;
        $user->fullname = $fullname;
        $user->birthday = $birthday;
        $user->address = $address;
        $user->sidn = $sidn;
        $user->gender = $gender;
        $user->phone = $phone;
        $user->disable = 0;

        // generate pin code for driver, staff, subdriver, teller, collecter
        if (
            $role->name == 'driver' || $role->name == 'staff' ||
            $role->name == 'subdriver' || $role->name == 'teller' ||
            $role->name == 'collecter' || $role->name == 'accountant' || $role->name == 'executive'
        ) {

            // set into user
            $digits = 6;
            $pin_flag = false;
            $pin_code = '';

            while (!$pin_flag) {

                // check pin code exist
                $pin_code = str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);

                if (!$this->checkExistsByKey('pin_code', $pin_code)) {
                    $pin_flag = true;
                }
            }

            $user->pin_code = $pin_code;
        }

        if ($user->save()) {

            $user = $user->toArray();
            $user['rfid'] = empty($rfidcard) ? null : $rfidcard->rfid;
            unset($user['created_at']);
            unset($user['updated_at']);

            $push_log = [];
            $push_log['action'] = 'create';
            $push_log['company_id'] = $company_id;
            $push_log['subject_id'] = $user['id'];
            $push_log['subject_type'] = 'user';
            $push_log['subject_data'] = $user;
            $this->push_logs->createPushLog($push_log);

            // update taget for rfid card
            if (!empty($rfidcard_id)) {
                $this->rfidcards->updateTargetAndUsage($rfidcard_id, $company_id, $user['id'], 'user');
            }
            return $this->getUserByKey('id', $user['id'], $company_id);
        }

        return response('Create Error', 404);
    }

    public function updateUser($data)
    {
        $id = $data['id'];
        $rfid = $data['rfid'] ?? null;
        $role_id = (int) $data['role_id'];
        $email = $data['email'] ?? null;
        $company_id = $data['company_id'];
        $fullname = $data['fullname'] ?? null;
        $birthday = $data['birthday'] ?? null;
        $address = $data['address'] ?? null;
        $sidn = $data['sidn'] ?? null;
        $gender = $data['gender'];
        $phone = $data['phone'] ?? null;

        // get user by id
        $user = User::find($id);

        if (empty($user)) {
            return response('User not found.', 404);
        }

        // check role
        $role = $this->roles->getRoleById($role_id);

        if (empty($role)) {
            return response('Role not found.', 404);
        }
        $role_name = $role->name;

        // get current role
        $data_role = $this->roles->getRoleById($user->role_id);
        $current_role_name = $data_role->name;

        // check change role

        if ($role_name == $current_role_name && empty($user->pin_code)) {

            // generate pin code for driver, staff, subdriver, teller , collecter
            if (
                $role->name == 'driver' || $role->name == 'staff' ||
                $role->name == 'subdriver' || $role->name == 'teller' ||
                $role->name == 'collecter' || $role->name == 'accountant' || $role->name == 'executive'
            ) {

                $digits = 6;
                $pin_code = '';
                $pin_flag = false;

                if (empty($user->pin_code)) {

                    while (!$pin_flag) {

                        // check pin code exist
                        $pin_code = str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);

                        if (!$this->checkExistsByKey('pin_code', $pin_code)) {
                            $pin_flag = true;
                        }
                    }

                    // set into user
                    $user->pin_code = $pin_code;
                }
            } else {
                $user->pin_code = null;
            }
        }

        if ($role_name != $current_role_name) {

            // generate pin code for driver, staff, subdriver, teller , collecter
            if (
                $role->name == 'driver' || $role->name == 'staff' ||
                $role->name == 'subdriver' || $role->name == 'teller' ||
                $role->name == 'collecter' || $role->name == 'accountant' || $role->name == 'executive'
            ) {

                $digits = 6;
                $pin_code = '';
                $pin_flag = false;

                if (empty($user->pin_code)) {

                    while (!$pin_flag) {

                        // check pin code exist
                        $pin_code = str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);

                        if (!$this->checkExistsByKey('pin_code', $pin_code)) {
                            $pin_flag = true;
                        }
                    }

                    // set into user
                    $user->pin_code = $pin_code;
                }
            } else {
                $user->pin_code = null;
            }
        }

        if (empty($rfid)) {

            if ($user->rfidcard_id) {
                //remove card
                $this->rfidcards->updateTargetAndUsage($user->rfidcard_id, $company_id, null, null);
            }

            $user->rfidcard_id = null;
        } else {

            // check rfidcard
            $rfidcard = $this->rfidcards->getRfidCardByRfid($rfid);

            if (empty($rfidcard)) {
                return response('Rfid card not found.', 404);
            }

            // get current rfid
            if ($user->rfidcard_id) {

                $data_rfidcard = $this->rfidcards->getRfidCardById($user->rfidcard_id);
                $current_rfid = $data_rfidcard->rfid;

                if ($rfid != $current_rfid) {

                    if ($this->rfidcards->checkRfidCardUsed($rfidcard->rfid, $rfidcard->barcode)) {
                        return response('The rfid card has been used.', 404);
                    }

                    // update new card
                    $this->rfidcards->updateTargetAndUsage(
                        $rfidcard->id,
                        $company_id,
                        $user->id,
                        'user'
                    );

                    //remove old card
                    $this->rfidcards->updateTargetAndUsage(
                        $data_rfidcard->id,
                        $company_id,
                        null,
                        null
                    );
                }
            } else {

                if ($this->rfidcards->checkRfidCardUsed($rfidcard->rfid, $rfidcard->barcode)) {
                    return response('The rfid card has been used.', 404);
                }

                // update new card
                $this->rfidcards->updateTargetAndUsage(
                    $rfidcard->id,
                    $company_id,
                    $user->id,
                    'user'
                );
            }

            // set into user
            $user->rfidcard_id = $rfidcard->id;
        }

        $user->role_id = intval($role_id);
        $user->company_id = $company_id;
        $user->email = $email;
        $user->fullname = $fullname;
        $user->birthday = $birthday;
        $user->address = $address;
        $user->sidn = $sidn;
        $user->gender = $gender;
        $user->phone = $phone;

        if ($user->save()) {

            $user->rfid = isset($user->rfidcard_id) ? $rfidcard->rfid : null;
            $user = $user->toArray();
            unset($user['created_at']);
            unset($user['updated_at']);

            $push_log = [];
            $push_log['action'] = 'update';
            $push_log['company_id'] = $company_id;
            $push_log['subject_id'] = $user['id'];
            $push_log['subject_type'] = 'user';
            $push_log['subject_data'] = $user;
            $this->push_logs->createPushLog($push_log);

            return $this->getUserByKey('id', $user['id'], $company_id);
        }

        return response('Update Error', 404);
    }

    public function deleteUser($id, $company_id = null)
    {
        // check id
        if (empty($id) || (int) $id < 0)
            return response('Invalid ID supplied', 404);

        if (empty($company_id)) {
            $user = User::find($id);
        } else {
            $user = User::where('id', $id)
                ->where('company_id', $company_id)
                ->first();
        }

        if (empty($user))
            return response('User Not found', 404);

        //remove use rfidcard
        if (!empty($user->rfidcard_id)) {
            $this->rfidcards->updateTargetAndUsage($user->rfidcard_id, $user->company_id, null, null);

            $push_logs = $this->push_logs->getPushLogByOptions([
                ['subject_id', $user->rfidcard_id],
                ['subject_type', 'rfidcard']
            ]);
            if (count($push_logs) > 0) {
                foreach ($push_logs as $push_log) {
                    $push_log->delete();
                }
            }
        }

        //
        if (!empty($user->company_id)) {
            $push_log = [];
            $push_log['action'] = 'delete';
            $push_log['company_id'] = $user->company_id;
            $push_log['subject_id'] = $user->id;
            $push_log['subject_type'] = 'user';
            $push_log['subject_data'] = null;
            $this->push_logs->createPushLog($push_log);
        }

        if ($user->delete()) {
            return response('OK', 200);
        }

        return response('Delete Error', 404);
    }

    public function disableUser($data)
    {

        $id = $data['id'];
        $disable = $data['disable'];
        $company_id = $data['company_id'];

        // check id
        if (empty($id) || (int) $id < 0)
            return response('Invalid ID supplied', 404);

        if (empty($company_id)) {
            $user = User::find($id);
        } else {
            $user = User::where('id', $id)
                ->where('company_id', $company_id)
                ->first();
        }

        if (empty($user))
            return response('User Not found', 404);

        if ($disable == 0) {

            $user->disable = 1;

            if ($user->save()) {

                if (isset($user->rfidcard_id)) {
                    $rfidcard = $this->rfidcards->getRfidCardById($user->rfidcard_id);
                    $user->rfid = $rfidcard ? $rfidcard->rfid : null;
                }

                $user = $user->toArray();
                unset($user['created_at']);
                unset($user['updated_at']);

                $push_log = [];
                $push_log['action'] = 'update';
                $push_log['company_id'] = $company_id;
                $push_log['subject_id'] = $user['id'];
                $push_log['subject_type'] = 'user';
                $push_log['subject_data'] = $user;
                $this->push_logs->createPushLog($push_log);

                return response('Disable is success', 200);
            }
        }

        if ($disable == 1) {
            $user->disable = 0;
            if ($user->save()) {

                if (isset($user->rfidcard_id)) {
                    $rfidcard = $this->rfidcards->getRfidCardById($user->rfidcard_id);
                    $user->rfid = $rfidcard ? $rfidcard->rfid : null;
                }
                $user = $user->toArray();
                unset($user['created_at']);
                unset($user['updated_at']);

                $push_log = [];
                $push_log['action'] = 'update';
                $push_log['company_id'] = $company_id;
                $push_log['subject_id'] = $user['id'];
                $push_log['subject_type'] = 'user';
                $push_log['subject_data'] = $user;
                $this->push_logs->createPushLog($push_log);

                return response('Enable is success', 200);
            }
        }
    }

    public function getListUser($data)
    {
        $limit = $data['limit'];
        $role = $data['role'];

        if (empty($limit) && $limit < 0)
            $limit = 10;

        // if manager else admin
        if ($role == 'manager') {

            $company_id = $data['company_id'];
            $user_id = $data['user_id'];

            $pagination = User::join('roles', 'roles.id', '=', 'users.role_id')
                ->where('users.company_id', $company_id)
                ->where('roles.name', '!=', 'manager')
                ->with('role', 'company', 'rfidcard')
                ->select('users.*', 'roles.name as role_name')
                ->orderBy('users.fullname', 'ASC')
                ->paginate($limit)
                ->toArray();
        } else {

            // role id of manager
            $role_id = $data['role_id'];
            $pagination = User::where('role_id', $role_id)
                ->with('role', 'company', 'rfidcard')
                ->orderBy('fullname', 'ASC')
                ->paginate($limit)
                ->toArray();
        }

        header("pagination-total: " . $pagination['total']);
        header("pagination-current: " . $pagination['current_page']);
        header("pagination-last: " . $pagination['last_page']);

        return $pagination['data'];
    }

    public function getAllUser($data)
    {
        $role = $data['role'];
        // if manager else admin
        if ($role == 'manager') {

            $company_id = $data['company_id'];

            $users = User::where('company_id', $company_id)
                ->with('role', 'company', 'rfidcard')
                ->orderBy('fullname', 'ASC')
                ->get()
                ->toArray();
        } else {

            // role id of manager
            $role_id = $data['role_id'];
            $users = User::where('role_id', $role_id)
                ->with('role', 'company', 'rfidcard')
                ->orderBy('fullname', 'ASC')
                ->get()
                ->toArray();
        }

        return $users;
    }

    public function getUserById($id)
    {
        return  User::where('id', $id)
            ->with('role', 'company', 'rfidcard')
            ->first();
        return false;
    }

    public function getUserByKey($key, $value, $company_id = null)
    {
        if (!empty($company_id)) {
            $user = User::where($key, '=', $value)
                ->where('company_id', '=', $company_id)
                ->with('role', 'company', 'rfidcard')
                ->first();
        } else {
            $user = User::where($key, '=', $value)
                ->with('role', 'company', 'rfidcard')
                ->first();
        }

        if ($user) {

            // get permisstion by role id
            $role_id = $user->role->id;
            $permissions = PermissionRole::where('role_id', '=', $role_id)->get()->toArray();
            $perm_arr = [];

            if (count($permissions) > 0) {

                foreach ($permissions as $permission) {

                    $per = Permission::find($permission['permission_id']);

                    if (!empty($per)) {
                        array_push($perm_arr, $per);
                    }
                }
            }
            $user->permissions = $perm_arr;
        }

        return $user;
    }

    public function getUsersById($id)
    {
        $user =  User::where('id', $id)
            ->with('role', 'company', 'rfidcard')
            ->first();
        return $user;
    }

    public function getUsersByIdAndCompanyId($id, $company_id)
    {
        $user =  User::where('id', $id)
            ->where('company_id', $company_id)
            ->with('role', 'company', 'rfidcard')
            ->first();
        return $user;
    }

    public function changePassword($user_id, $data)
    {
        $password = $data['current_password'];
        $new_password = $data['new_password'];
        $confirm_password = $data['confirm_password'];

        $user = User::find($user_id);

        if (empty($user)) return response('Not found', 404);

        // check current password
        if (!Hash::check($password, $user->password))
            return response('The current password is incorrect.', 404);

        // check match
        if ($new_password != $confirm_password)
            return response('Password does not match the confirm password.', 404);

        //update password
        $user->password = Hash::make($new_password);

        if ($user->save())
            return $this->getUserByKey('id', $user['id']);

        return response('Change password error.', 404);
    }

    public function changeInforUser($data)
    {
        $id = $data['id'];
        $role_id = (int) $data['role_id'];
        $email = $data['email'] ?? null;
        $company_id = $data['company_id'];
        $fullname = $data['fullname'] ?? null;
        $birthday = $data['birthday'] ?? null;
        $address = $data['address'] ?? null;
        $sidn = $data['sidn'] ?? null;
        $gender = $data['gender'];
        $phone = $data['phone'] ?? null;

        // get user by id
        $user = User::where('id',$id)->with('rfidcard')->first();

        if (empty($user)) {
            return response('User not found.', 404);
        }

        // check role
        $role = $this->roles->getRoleById($role_id);

        if (empty($role)) {
            return response('Role not found.', 404);
        }

        $user->role_id = intval($role_id);
        $user->company_id = $company_id;
        $user->email = $email;
        $user->fullname = $fullname;
        $user->birthday = $birthday;
        $user->address = $address;
        $user->sidn = $sidn;
        $user->gender = $gender;
        $user->phone = $phone;

        if ($user->save()) {

            $user->rfid = isset($user->rfidcard) ? $user->rfidcard->rfid : null;
            $user = $user->toArray();
            unset($user['created_at']);
            unset($user['updated_at']);

            $push_log = [];
            $push_log['action'] = 'update';
            $push_log['company_id'] = $company_id;
            $push_log['subject_id'] = $user['id'];
            $push_log['subject_type'] = 'user';
            $push_log['subject_data'] = $user;
            $this->push_logs->createPushLog($push_log);

            return $this->getUserByKey('id', $user['id'], $company_id);
        }

        return response('Update Error', 404);
    }

    public function searchUser($data)
    {
        $rfid = $data['rfid'];
        $barcode = $data['barcode'];
        $company_id = $data['company_id'];
        $rfidcard = null;

        if (!empty($rfid)) {
            $rfidcard = $this->rfidcards->searchRfidCardByOptions([
                ['rfid', $rfid]
            ]);
        }

        if (!empty($barcode)) {
            $rfidcard = $this->rfidcards->searchRfidCardByOptions([
                ['barcode', $barcode]
            ]);
        }

        if (!empty($rfidcard)) {

            $rfidcard_id = $rfidcard->id;

            $user = $this->getUserByKey('rfidcard_id', $rfidcard_id, $company_id);

            if ($user) return $user;
        }

        return response('Data not found', 404);
    }

    public function getListUserByInputName($data)
    {
        $company_id = $data['company_id'];
        $key_input = $data['key_input'];
        $style_search = $data['style_search'];
        $user_id = $data['user_id'];

        $user = User::join('roles', 'roles.id', '=', 'users.role_id')
              ->where('users.company_id', $company_id)
              ->where('roles.name', '!=', 'manager')
              ->where('users.id', '!=', $user_id);

        if ($style_search == 'name') {
            $user->where('fullname', 'like', "%$key_input%");
        }
        if ($style_search == 'pincode') {
            $user->where('pin_code', 'like', "%$key_input%");
        }
        if ($style_search == 'phone') {
            $user->where('phone', 'like', "%$key_input%");
        }
        if ($style_search == 'role') {
            $user->where('role_id',$key_input);
        }

        return $user->with('role', 'company', 'rfidcard')
              ->select('users.*', 'roles.name as role_name')
              ->orderBy('fullname', 'ASC')
              ->get()->toArray();
    }

    public function getUsersByOptions($options = [])
    {

        if (count($options) > 0) {
            foreach ($options as $key => $option) {
                if (count($option) == 2 && empty($option[1]))
                    unset($options[$key]);
            }

            return User::where($options)
                ->with('role', 'company', 'rfidcard')
                ->orderBy('role_id')
                ->get();
        }

        return response('User Not found', 404);
    }

    public function getUsersByRoleAndCompany($roles, $companyId)
    {
        return User::where('company_id', $companyId)
            ->whereIn('role_id', $roles)
            ->with('role', 'company', 'rfidcard')
            ->orderBy('role_id')
            ->get();
    }

    public function getUsersByIdReturnArray($id){
        return User::where('id', $id)->with('role', 'company', 'rfidcard')->get();
    }
}
