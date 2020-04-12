<?php

namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\MembershipsTmpService;

class ManagerMembershipsTmpApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\MembershipsTmpService
     */
    protected $memberships_tmp;

    public function __construct(
        Request $request,
        MembershipsTmpService $memberships_tmp
    )
    {
        $this->request = $request;
        $this->memberships_tmp = $memberships_tmp;
    }

    /**
     * Operation managerlistMembershipsTmp
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerlistMembershipsTmp()
    {
      // check login
      $user = $this->requiredAuthUser();
      if (empty($user)) return response('token_invalid', 401);

      $input = $this->request->all();

      return $this->memberships_tmp->getMembershipsTmp($input, $user->company_id);
    }
    /**
     * Operation managerListMembershipsTmpByInputAndByTypeSearch
     *
     * List of Memberships Tmp by search.
     *
     *
     * @return Http response
     */
    public function managerListMembershipsTmpByInputAndByTypeSearch()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->memberships_tmp->getListMembershipsTmpByInputAndByTypeSearch($input);
    }
    /**
     * Operation managerGetMembershipTmpById
     *
     * Find by ID.
     *
     * @param int $membership_tmp_id  (required)
     *
     * @return Http response
     */
    public function managerGetMembershipTmpById($membership_tmp_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return $this->memberships_tmp->getMembershipsTmpById($membership_tmp_id);
    }

    /**
     * Operation managerDeleteMembershipTmp
     *
     * delete by ID.
     *
     * @param int $membership_tmp_id  (required)
     *
     * @return Http response
     */
    public function managerDeleteMembershipTmp($membership_tmp_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return $this->memberships_tmp->deleteMembershipTmpById($membership_tmp_id);
    }

    /**
     * Operation managerAcceptMembershipsTmp
     *
     * Accept membership tmp.
     *
     *
     * @return Http response
     */
    public function managerAcceptMembershipsTmp()
    {
      // check login
      $user = $this->requiredAuthUser();
      if (empty($user)) return response('token_invalid', 401);

      //path params validation
      $this->validate($this->request, [
          'membershiptype_id' => 'required|integer|min:1',
          'expiration_date'=> 'required',
          'start_expiration_date'=> 'required',
          'rfid'=>  'required',
          'fullname'=>  'required'
      ]);

      $input = $this->request->all();
      $input['company_id'] = $user->company_id;
      $input['user_id'] = $user->id;

      return $this->memberships_tmp->acceptMembershipsTmp($input);
    }
}
