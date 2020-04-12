<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

/**
 * @property int $id
 * @property int $company_id
 * @property int $role_id
 * @property int $rfidcard_id
 * @property string $username
 * @property string $password
 * @property string $email
 * @property string $fullname
 * @property string $birthday
 * @property string $address
 * @property string $sidn
 * @property boolean $gender
 * @property string $phone
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Company $company
 * @property RfidCard $rfidcard
 * @property Position $position
 * @property Role $role
 * @property Session[] $sessions
 * @property Shift[] $shifts
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject 
{
    use SoftDeletes;
    use Notifiable;
    use Authenticatable, Authorizable;

    /**
     * @var array
     */
    protected $fillable = ['company_id', 'role_id', 'rfidcard_id', 'username', 'password', 'email', 'fullname', 'birthday', 'address', 'sidn', 'gender', 'phone', 'created_at', 'updated_at', 'deleted_at', 'pin_code', 'disable'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function rfidcard()
    {
        return $this->belongsTo('App\Models\RfidCard');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sessions()
    {
        return $this->hasMany('App\Models\Session');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shifts()
    {
        return $this->hasMany('App\Models\Shift');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }      
}
