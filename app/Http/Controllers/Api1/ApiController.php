<?php

namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Factory as Auth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use App\Models\User;
use JWTAuth;

class ApiController extends Controller
{
    protected $token;

    /**
     * @var Illuminate\Contracts\Auth\Factory
     */
    protected $auth;


    public function __construct(Auth $auth)
    {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $this->token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
        }

        if (!empty($this->token)) {
            JWTAuth::setToken($this->token);
            JWTAuth::toUser();
        }

        $this->auth = $auth;
    }


    public function requiredAuthUser()
    {
        try {
            if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
                $this->token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
                JWTAuth::setToken($this->token);
                return JWTAuth::toUser();
            }
        } catch (Exception $e) {
            return null;
            // if ($e instanceof TokenInvalidException) {
            //     return response('Token is invalid', 500);
            // } else if ($e instanceof TokenExpiredException) {
            //     return response('Token is expired', 500);
            // } else {
            //     return response('Something is wrong', 500);
            // }
        }
        
    }

    public function loginById($user_id)
    {
        $user = User::find($user_id);
        $this->token = JWTAuth::fromUser($user);
        JWTAuth::setToken($this->token);
        JWTAuth::toUser();
    }

    public function getToken()
    {
        return $this->token;
    }

}