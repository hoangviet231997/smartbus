<?php
namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\PartnersService;
use App\Services\GroupKeyCompaniesService;
use App\Services\PublicFunctionService;

class AdminGroupKeyApi extends ApiController
{
     /**
     * @var Illuminate\Http\Request
     */
    protected $request;

     /**
     * @var  App\Services\GroupKeyCompaniesService;
     */
    protected $group_key_companies;
    protected $partner_codes;

    /**
     * Constructor
     */
    public function __construct(
        Request $request,
        GroupKeyCompaniesService $group_key_companies,
        PartnersService $partner_codes,
        PublicFunctionService $public_functions
    )
    {
        $this->request = $request;
        $this->group_key_companies = $group_key_companies;
        $this->partner_codes = $partner_codes;
        $this->public_functions = $public_functions;
    }

    /**
     * Operation createGroupKeyCompanies
     *
     * create.
     *
     *
     * @return Http response
     */
    public function createGroupKeyCompanies()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'name' => 'required',
            'companies' => 'nullable'
        ]);

        // save Company
        $input = $this->request->all();

        return $this->group_key_companies->createGroupKeyCompanies($input);
    }
    /**
     * Operation listGroupKeyCompanies
     *
     * list.
     *
     *
     * @return Http response
     */
    public function listGroupKeyCompanies()
    {
        //check login
        $user = $this->requiredAuthUser();
        if (empty($user)) {
            return response('token_invalid',401);
        }

        return $this->group_key_companies->listGroupKeyCompanies();
    }

    /**
     * Operation getGroupKeyCompaniesById
     *
     * Find by ID.
     *
     * @param int $group_key_id  (required)
     *
     * @return Http response
     */
    public function getGroupKeyCompaniesById($group_key_id){
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($group_key_id) || (int)$group_key_id < 0) 
            return response('Invalid ID supplied', 404);

        //get Group Key Company

        $group_key = $this->group_key_companies->getGroupKeyCompaniesById($group_key_id);

        if (empty($group_key)) {
            return response('Group Company Not found',404);
        }

        return $group_key;

    }

    /**
     * Operation updateGroupKeyCompanies
     *
     * update.
     *
     *@param int $group_key_id  (required)
     * @return Http response
     */
    public function updateGroupKeyCompanies()
    {
        // check login
        $user = $this->requiredAuthUser();
        if(empty($user)) return response('token_invalid', 401);

        // path params validation
        $this->validate($this->request,[
            'name' => 'required',
            'companies' => 'nullable'
        ]);

        // save group key company
        $input = $this->request->all();

        return $this->group_key_companies->updateGroupKeyCompanies($input);
    }

    /**
     * Operation updateGroupKeyCompanies
     *
     * delete.
     *@param int $group_key_id  (required)
     *
     * @return Http response
     */
    public function deleteGroupKeyCompaniesById($group_key_id){
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($group_key_id) || (int)$group_key_id < 0) 
            return response('Invalid ID supplied', 404);

        return $this->group_key_companies->deleteGroupKeyCompanies($group_key_id);
    }

    public function listGroupKeyCompaniesByTypeForApp($type = null)
    {
        $token = $this->request->header('Token');
        $partner_code = $this->request->header('partnerCode');

        if (empty($token)) return ['status' => false, 'message' => 'Mã token không tìm thấy', 'data' => []];
        if (empty($partner_code)) return ['status' => false, 'message' => 'Mã đối tác không tìm thấy', 'data' => []];
        if ($type == null) return ['status' => false, 'message' => 'Tham số đường truyền không tìm thấy', 'data' => []];

        $partner = $this->partner_codes->getPartnerByPartnerCode($partner_code);
        if (empty($partner)) return ['status' => false, 'message' => 'Mã đối tác không tồn tại', 'data' => []];

        $app_key = $partner->app_key;

        $token_de = json_decode($this->public_functions->deCrypto($token, $partner_code));
        if (empty($token_de)) return ['status' => false, 'message' => 'Giải mã token thất bại', 'data' => []];

        $app_key_de = $token_de->appKey ?? null;
        $timestamp = $token_de->timestamp ?? null;

        if($app_key_de == null || $timestamp == null){ return ['status'=> false, 'message'=> 'Giải mã token thất bại', 'data' => []]; }
        if($app_key_de != $app_key){ return ['status'=> false, 'message'=> 'Giải mã token thất bại', 'data' => []]; }

        $diff_time = $this->public_functions->s_datediff('i',date('d-m-y H:i:s'),date('d-m-y H:i:s',$timestamp));
        if ($diff_time > 10) return ['status'=> false, 'message'=> 'Hết thời gian gọi API', 'data' => []];

        return $this->group_key_companies->listGroupKeyCompaniesByTypeForApp($type);
    }
}
