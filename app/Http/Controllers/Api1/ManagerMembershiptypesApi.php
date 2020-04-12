<?php

namespace App\Http\Controllers\Api1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;

use App\Services\MembershipTypeService;

class ManagerMembershiptypesApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\MembershipTypeService
     */
    protected $membershipType;    

    /**
     * Constructor
     */
    public function __construct(Request $request, MembershipTypeService $membershipType)
    {
        $this->request = $request;
        $this->membershipType = $membershipType;
    }

    /**
     * Operation managerListMembershipTypes
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerListMembershipTypes()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return $this->membershipType->getList($user->company_id);
    }

    /**
     * Operation managerUpdateMembershipType
     *
     * update.
     *
     *
     * @return Http response
     */
    public function managerUpdateMembershipType()
    {
       // check login
       $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);

        //path params validation
        if(!$user->company_id){
            $this->validate($this->request, [
                'name' => 'required',
                'deduction'=> 'required|integer|min:0|max:100',
                'code' => 'required',
                'company_id' => 'required'
        ]);
        }else{
            $this->validate($this->request, [
                'name' => 'required',
                'deduction'=> 'required|integer|min:0|max:100',
                'code' => 'required'
            ]);
        }

       // save
       $input = $this->request->all();
       if($user->company_id){ $input['company_id'] = $user->company_id;}

       return $this->membershipType->updated($input);
    }
    /**
     * Operation manmagerCreateMembershipType
     *
     * create.
     *
     *
     * @return Http response
     */
    public function manmagerCreateMembershipType()
    {
        
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        if(!$user->company_id){
            $this->validate($this->request, [
                'name' => 'required',
                'deduction'=> 'required|integer|min:0|max:100',
                'code' => 'required',
                'company_id' => 'required'
        ]);
        }else{
            $this->validate($this->request, [
                'name' => 'required',
                'deduction'=> 'required|integer|min:0|max:100',
                'code' => 'required'
            ]);
        }
        // save
        $input = $this->request->all();
       
        if($user->company_id){ $input['company_id'] = $user->company_id;}
       
        return $this->membershipType->created($input);
    }
    /**
     * Operation managerDeleteMembershipType
     *
     * Delete.
     *
     * @param int $membershiptype_id  (required)
     *
     * @return Http response
     */
    public function managerDeleteMembershipType($membershiptype_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // save
        $input = $this->request->all();
        if($user->company_id){ $input['company_id'] = $user->company_id;}
        $input['id'] = $membershiptype_id;

        return $this->membershipType->deleted($input);
    }
    /**
     * Operation managerGetMembershipTypeById
     *
     * Find by ID.
     *
     * @param int $membershiptype_id  (required)
     *
     * @return Http response
     */
    public function managerGetMembershipTypeById($membershiptype_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // save
        $input = $this->request->all();
        $input['id'] = (int)$membershiptype_id;

        if($user->company_id){ $input['company_id'] = $user->company_id;}
       
        return $this->membershipType->getById($membershiptype_id);
    }
}
