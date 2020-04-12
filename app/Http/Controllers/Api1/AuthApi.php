<?php
namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Contracts\Auth\Factory as Auth;
use JWTAuth;
use App\Models\Session;
use App\Models\User;
use App\Models\PermissionRole;
use App\Models\Permission;
use App\Models\Company;
use App\Models\Notify;
use App\Models\NotifyType;
use App\Models\Membership;
use DB;

class AuthApi extends ApiController
{
    /**
     * Operation login
     * @return Http response
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only('username', 'password');
        $token = null;

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {

            return response()->json(['token_expired'], 401);
        } catch (TokenInvalidException $e) {

            return response()->json(['token_invalid'], 401);
        } catch (JWTException $e) {

            return response()->json(['token_absent' => $e->getMessage()], 401);
        }

        $user = $this->auth->user();
        $user->role;
        $user->rfidcard;
        $user_id = $user->id;
        $role_id = $user->role->id;
        $user->company;

        $company_id = $user->company ? $user->company->id : 0;

        //check disable
        if($user->disable == 1){
            return response()->json(['user_not_found'], 404);
        }

        //check permisstion
        $accepts = ['admin', 'manager', 'teller', 'collecter', 'staff', 'accountant', 'executive'];

        //check company has permission for driver and subdriver
        if($user->role->name == 'driver' || $user->role->name == 'subdriver'){
          $check_permisson = PermissionRole::where('role_id', $role_id)->where('company_id', $company_id)->exists();
          if($check_permisson) array_push($accepts, "driver", "subdriver");
        }

        if ( !in_array( $user->role->name, $accepts ) ) {
            return response()->json(['user_not_found'], 404);
        }
        // get permisstion by role id
        $permissions = PermissionRole::where('role_id', $role_id)->where('company_id', $company_id)->get()->toArray();
        $perm_arr = [];

        if (count($permissions) > 0) {

            foreach ($permissions as $permission) {

                $per = Permission::find($permission['permission_id']);
                $per->key_tools = json_decode($per->key_tools, true);
                $perm_arr[$per->key] = $per;

                //if (!empty($per)) array_push($perm_arr, $per);
            }
        }

        $user->permissions = $perm_arr;

        if($user->role->name != 'admin'){
          if($user->role->name == 'driver' || $user->role->name == 'subdriver'){
            if(count($user->permissions) == 0 || !isset($user->permissions['sub_dashboard']))
                return response()->json(['permission_denied'], 404);
          }
          if(in_array($user->role->name, ['manager', 'teller', 'collecter', 'staff', 'accountant', 'executive'])){
            if(count($user->permissions) == 0 || !isset($user->permissions['dashboard'])){
              return response()->json(['permission_denied'], 404);
            }
          }
        }

        if ($user_id > 0) {

            $agent = '';
            $ip_addr = '';
            $mac_addr = '';

            if (isset($_SERVER['HTTP_USER_AGENT'])) $agent = $_SERVER['HTTP_USER_AGENT'];

            if (isset($_SERVER['REMOTE_ADDR'])) $ip_addr = $_SERVER['REMOTE_ADDR'];

            if (isset($_SERVER['MAC_ADDR'])) $mac_addr = $_SERVER['MAC_ADDR'];

            $session = new Session();
            $session->user_id = $user_id;
            $session->token = $token;
            $session->ip_address = $ip_addr;
            $session->agent = $agent;
            $session->mac = $mac_addr;
            $session->save();
        }

        //Check time in file notify-date.json
        // if($user->role->name == 'manager'){
            if(isset($user->permissions['web_notifies'])){

                $path = public_path()."/file/notify-date.txt";
                $date_now = date('Y-m-d');
                $notify_date = "" ;

                if(file_exists($path)) $notify_date = file_get_contents($path);

                if($notify_date != ""){

                    $notify_date = json_decode($notify_date, true);

                    if($notify_date != null){

                        if(isset($notify_date[$company_id])){

                            if($notify_date[$company_id] <  $date_now){

                                $notify_date[$company_id] = $date_now;

                                if(file_put_contents($path,json_encode($notify_date))){

                                    //"mbs_expired" - The het han
                                    //"mbs_register" - Dang ky the qua app
                                    $notify_type = NotifyType::where('key', '=', 'mbs_expired')->first();

                                    if(!empty($notify_type)){

                                        // return $notify_arr;
                                        $mbs_expireds = Membership::join('membership_types', 'membership_types.id', '=', 'memberships.membershiptype_id')
                                                        ->whereRaw('
                                                            (
                                                                memberships.company_id = '.$company_id.'
                                                                AND memberships.id NOT IN (SELECT subject_id FROM notifies where notify_type_id = '.$notify_type->id.')
                                                                AND ((CASE
                                                                    WHEN membership_types.code = 1
                                                                    THEN  PERIOD_DIFF(
                                                                        CAST(REPLACE(memberships.expiration_date, "-", "") as unsigned),
                                                                        CAST("' . date('Ym') . '" as unsigned)
                                                                    )
                                                                END) < 0)
                                                            )OR(
                                                                memberships.company_id = '.$company_id.'
                                                                AND memberships.id NOT IN (SELECT subject_id FROM notifies where notify_type_id = '.$notify_type->id.')
                                                                AND ((CASE
                                                                    WHEN membership_types.code = 0
                                                                    THEN  DATEDIFF(memberships.expiration_date,"'.date('Y-m-d').'")
                                                                END) <= 7)
                                                            )
                                                        ')
                                                        ->select(
                                                            'memberships.*',
                                                            'membership_types.code',
                                                            DB::raw('
                                                            (CASE
                                                                WHEN membership_types.code = 1
                                                                THEN  PERIOD_DIFF(
                                                                    CAST(REPLACE(memberships.expiration_date, "-", "") as unsigned),
                                                                    CAST("'.date('Ym').'" as unsigned)
                                                                )
                                                                ELSE  DATEDIFF(memberships.expiration_date,"'.date('Y-m-d').'")
                                                            END) AS duration_date'))
                                                        ->get();

                                        foreach ($mbs_expireds as $key => $mbs_expired) {

                                            $title = "Thẻ ";

                                            if($mbs_expired->code == 0){

                                                if($mbs_expired->fullname != null) $title .= "<strong>".$mbs_expired->fullname."</strong> ";
                                                else $title .= "trả trước này ";

                                                if($mbs_expired->duration_date > 0) $title .= "còn lại <strong>".$mbs_expired->duration_date." ngày </strong> nữa hết hạn sử dụng";
                                                else $title .= "đã hết hạn sử dụng";

                                            }else{
                                                if($mbs_expired->fullname != null) $title .= "<strong>".$mbs_expired->fullname."</strong> đã hết hạn sử dụng";
                                                else $title .= "tháng này đã hết hạn sử dụng";
                                            }

                                            $notify = new Notify();
                                            $notify->title = $title;
                                            $notify->company_id = $mbs_expired->company_id;
                                            $notify->subject_id = $mbs_expired->id;
                                            $notify->notify_type_id = $notify_type->id;
                                            $notify->readed = 0;

                                            unset($mbs_expired->created_at);
                                            unset($mbs_expired->updated_at);
                                            unset($mbs_expired->deleted_at);
                                            $notify->subject_data = json_encode($mbs_expired, JSON_UNESCAPED_UNICODE);
                                            $notify->save();
                                        }
                                    }
                                }
                            }
                        }else{

                            $notify_date[$company_id] = $date_now;

                            if(file_put_contents($path,json_encode($notify_date))){

                                //"mbs_expired" - The het han
                                //"mbs_register" - Dang ky the qua app
                                $notify_type = NotifyType::where('key', '=', 'mbs_expired')->first();

                                if(!empty($notify_type)){

                                    // return $notify_arr;
                                    $mbs_expireds = Membership::join('membership_types', 'membership_types.id', '=', 'memberships.membershiptype_id')
                                                    ->whereRaw('
                                                        (
                                                            memberships.company_id = '.$company_id.'
                                                            AND memberships.id NOT IN (SELECT subject_id FROM notifies where notify_type_id = '.$notify_type->id.')
                                                            AND ((CASE
                                                                WHEN membership_types.code = 1
                                                                THEN  PERIOD_DIFF(
                                                                    CAST(REPLACE(memberships.expiration_date, "-", "") as unsigned),
                                                                    CAST("' . date('Ym') . '" as unsigned)
                                                                )
                                                            END) < 0)
                                                        )OR(
                                                            memberships.company_id = '.$company_id.'
                                                            AND memberships.id NOT IN (SELECT subject_id FROM notifies where notify_type_id = '.$notify_type->id.')
                                                            AND ((CASE
                                                                WHEN membership_types.code = 0
                                                                THEN  DATEDIFF(memberships.expiration_date,"'.date('Y-m-d').'")
                                                            END) <= 7)
                                                        )
                                                    ')
                                                    ->select(
                                                        'memberships.*',
                                                        'membership_types.code',
                                                        DB::raw('
                                                        (CASE
                                                            WHEN membership_types.code = 1
                                                            THEN  PERIOD_DIFF(
                                                                CAST(REPLACE(memberships.expiration_date, "-", "") as unsigned),
                                                                CAST("'.date('Ym').'" as unsigned)
                                                            )
                                                            ELSE  DATEDIFF(memberships.expiration_date,"'.date('Y-m-d').'")
                                                        END) AS duration_date'))
                                                    ->get();

                                    foreach ($mbs_expireds as $key => $mbs_expired) {

                                        $title = "Thẻ ";

                                        if($mbs_expired->code == 0){

                                            if($mbs_expired->fullname != null) $title .= "<strong>".$mbs_expired->fullname."</strong> ";
                                            else $title .= "trả trước này ";

                                            if($mbs_expired->duration_date > 0) $title .= "còn lại <strong>".$mbs_expired->duration_date." ngày </strong> nữa hết hạn sử dụng";
                                            else $title .= "đã hết hạn sử dụng";

                                        }else{
                                            if($mbs_expired->fullname != null) $title .= "<strong>".$mbs_expired->fullname."</strong> đã hết hạn sử dụng";
                                            else $title .= "tháng này đã hết hạn sử dụng";
                                        }

                                        $notify = new Notify();
                                        $notify->title = $title;
                                        $notify->company_id = $mbs_expired->company_id;
                                        $notify->subject_id = $mbs_expired->id;
                                        $notify->notify_type_id = $notify_type->id;
                                        $notify->readed = 0;

                                        unset($mbs_expired->created_at);
                                        unset($mbs_expired->updated_at);
                                        unset($mbs_expired->deleted_at);
                                        $notify->subject_data = json_encode($mbs_expired, JSON_UNESCAPED_UNICODE);
                                        $notify->save();
                                    }
                                }
                            }
                        }
                    }
                }else{

                    $obj = new \stdClass;
                    $obj->$company_id = $date_now;

                    if(file_put_contents($path,json_encode($obj))){

                        //"mbs_expired" - The het han
                        //"mbs_register" - Dang ky the qua app
                        $notify_type = NotifyType::where('key', '=', 'mbs_expired')->first();

                        if(!empty($notify_type)){

                            // return $notify_arr;
                            $mbs_expireds = Membership::join('membership_types', 'membership_types.id', '=', 'memberships.membershiptype_id')
                                            ->whereRaw('
                                                (
                                                    memberships.company_id = '.$company_id.'
                                                    AND memberships.id NOT IN (SELECT subject_id FROM notifies where notify_type_id = '.$notify_type->id.')
                                                    AND ((CASE
                                                        WHEN membership_types.code = 1
                                                        THEN  PERIOD_DIFF(
                                                            CAST(REPLACE(memberships.expiration_date, "-", "") as unsigned),
                                                            CAST("' . date('Ym') . '" as unsigned)
                                                        )
                                                    END) < 0)
                                                )OR(
                                                    memberships.company_id = '.$company_id.'
                                                    AND memberships.id NOT IN (SELECT subject_id FROM notifies where notify_type_id = '.$notify_type->id.')
                                                    AND ((CASE
                                                        WHEN membership_types.code = 0
                                                        THEN  DATEDIFF(memberships.expiration_date,"'.date('Y-m-d').'")
                                                    END) <= 7)
                                                )
                                            ')
                                            ->select(
                                                'memberships.*',
                                                'membership_types.code',
                                                DB::raw('
                                                (CASE
                                                    WHEN membership_types.code = 1
                                                    THEN  PERIOD_DIFF(
                                                        CAST(REPLACE(memberships.expiration_date, "-", "") as unsigned),
                                                        CAST("'.date('Ym').'" as unsigned)
                                                    )
                                                    ELSE  DATEDIFF(memberships.expiration_date,"'.date('Y-m-d').'")
                                                END) AS duration_date'))
                                            ->get();

                            foreach ($mbs_expireds as $key => $mbs_expired) {

                                $title = "Thẻ ";

                                if($mbs_expired->code == 0){

                                    if($mbs_expired->fullname != null) $title .= "<strong>".$mbs_expired->fullname."</strong> ";
                                    else $title .= "trả trước này ";

                                    if($mbs_expired->duration_date > 0) $title .= "còn lại <strong>".$mbs_expired->duration_date." ngày </strong> nữa hết hạn sử dụng";
                                    else $title .= "đã hết hạn sử dụng";

                                }else{
                                    if($mbs_expired->fullname != null) $title .= "<strong>".$mbs_expired->fullname."</strong> đã hết hạn sử dụng";
                                    else $title .= "tháng này đã hết hạn sử dụng";
                                }

                                $notify = new Notify();
                                $notify->title = $title;
                                $notify->company_id = $mbs_expired->company_id;
                                $notify->subject_id = $mbs_expired->id;
                                $notify->notify_type_id = $notify_type->id;
                                $notify->readed = 0;

                                unset($mbs_expired->created_at);
                                unset($mbs_expired->updated_at);
                                unset($mbs_expired->deleted_at);
                                $notify->subject_data = json_encode($mbs_expired, JSON_UNESCAPED_UNICODE);
                                $notify->save();
                            }
                        }
                    }
                }
            }
        // }

        return response()->json(compact('token','user'));
    }

    /**
     * Operation logout
     * @return Http response
     */
    public function logout(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|integer|min:1',
            'token' => 'required|string',
        ]);

        $input = $request->all();

        // delete session
        $session = Session::where('user_id', $input['user_id'])
                        ->where('token', $input['token'])->first();

        if (!empty($session)) $session->delete();

        JWTAuth::setToken( $input['token'] )->invalidate();
        return response('OK', 200);
    }

    /**
     * Operation loginAsCompany
     *
     * login as company.
     *
     * @param int $company_id  (required)
     *
     * @return Http response
     */
    public function loginAsCompany($company_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($company_id) || (int)$company_id < 0)
            return response('Invalid ID supplied', 404);

        // get user by company id
        $user = User::where('company_id', $company_id)->with('role', 'rfidcard', 'company')->first();

        // get permisstion by role id
        $permissions = PermissionRole::where('role_id', $user->role_id)->where('company_id', $company_id)->get()->toArray();
        $perm_arr = [];

        if (count($permissions) > 0) {

            foreach ($permissions as $permission) {

                $per = Permission::find($permission['permission_id']);
                $per->key_tools = json_decode($per->key_tools, true);
                $perm_arr[$per->key] = $per;

                //if (!empty($per)) array_push($perm_arr, $per);
            }
        }

        $user->permissions = $perm_arr;

        if($user->role->name != 'admin')
            if(count($user->permissions) == 0 || !isset($user->permissions['dashboard']))
                return response()->json(['permission_denied'], 404);

        if (!empty($user)) {

            $token = JWTAuth::fromUser($user);
            $user_id = $user->id;

            if ($user_id > 0) {

                $agent = '';
                $ip_addr = '';
                $mac_addr = '';

                if (isset($_SERVER['HTTP_USER_AGENT'])) $agent = $_SERVER['HTTP_USER_AGENT'];

                if (isset($_SERVER['REMOTE_ADDR'])) $ip_addr = $_SERVER['REMOTE_ADDR'];

                if (isset($_SERVER['MAC_ADDR'])) $mac_addr = $_SERVER['MAC_ADDR'];

                $session = new Session();
                $session->user_id = $user_id;
                $session->token = $token;
                $session->ip_address = $ip_addr;
                $session->agent = $agent;
                $session->mac = $mac_addr;
                $session->save();
            }
            return response()->json(compact('token','user'));
        }
        return response('User Not found', 404);
    }
}
