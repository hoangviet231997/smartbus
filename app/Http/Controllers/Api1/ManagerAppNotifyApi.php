<?php

namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\AppNotifiesService;
use App\Services\PartnersService;
use App\Services\PublicFunctionService;
use Log;

class ManagerAppNotifyApi extends ApiController
{
    /**
     * Constructor
     */
    protected $request;
    protected $app_notifies;
    protected $partner_codes;
    protected $public_functions;


    public function __construct(Request $request, AppNotifiesService $app_notifies, PartnersService $partner_codes, PublicFunctionService $public_functions)
    {
        $this->request = $request;
        $this->app_notifies = $app_notifies;
        $this->partner_codes = $partner_codes;
        $this->public_functions = $public_functions;
    }

    /**
     * Operation managerCreateNotify
     *
     * create notify.
     *
     *
     * @return Http response
     */
    public function managerCreateAppNotify()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        //path params validation
        $this->validate($this->request, [
            'name' => 'required',
            'description' => 'required | max:256',
            'weigth' => 'required',
        ]);

        $input['company_id'] = $user->company_id;

        return $this->app_notifies->createAppNotify($input);
    }
    /**
     * Operation managerListNotify
     *
     * get all notify.
     *
     *
     * @return Http response
     */
    public function managerListAppNotify()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->app_notifies->listAppNotify($input);
    }
    /**
     * Operation managerUpdateNotify
     *
     * update notify.
     *
     *
     * @return Http response
     */
    public function managerUpdateAppNotify()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        //path params validation
        $this->validate($this->request, [
          'name' => 'required',
          'description' => 'required | max:256',
          'weigth' => 'required',
        ]);

        $input['company_id'] = $user->company_id;

        return $this->app_notifies->updateAppNotify($input);
    }
    /**
     * Operation managerSearchAppNotifyByInput
     *
     * search app notify by input.
     *
     *
     * @return Http response
     */
    public function managerSearchAppNotifyByInput()
    {
      // check login
      $user = $this->requiredAuthUser();
      if (empty($user)) return response('token_invalid', 401);

      $input = $this->request->all();
      $input['company_id'] = $user->company_id;

      return $this->app_notifies->searchAppNotifyByInput($input);
    }
    /**
     * Operation managerDeleteAppNotify
     *
     * delete notify.
     *
     * @param int $notify_id  (required)
     *
     * @return Http response
     */
    public function managerDeleteAppNotify($app_notify_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return $this->app_notifies->deleteAppNotify($app_notify_id);
    }
    /**
     * Operation managerGetNotifyById
     *
     * Find by ID.
     *
     * @param int $notify_id  (required)
     *
     * @return Http response
     */
    public function managerGetAppNotifyById($app_notify_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return $this->app_notifies->getAppNotifyById($app_notify_id);
    }

    public function managerGetAppNotifyForApp()
    {
        $token = $this->request->header('Token');
        $partner_code = $this->request->header('partnerCode');

        if(empty($token)) {  return ['status'=> false, 'message'=> 'Mã token không tìm thấy', 'data' => []]; }
        if(empty($partner_code)) {  return ['status'=> false, 'message'=> 'Mã đối tác không tìm thấy', 'data' => []]; }

        $partner = $this->partner_codes->getPartnerByPartnerCode($partner_code);

        if(empty($partner)){ return ['status'=> false, 'message'=> 'Mã đối tác không tồn tại', 'data' => []];  }

        $app_key = $partner->app_key;

        $token_de = json_decode($this->public_functions->deCrypto($token, $partner_code));
        if(empty($token_de)){ return ['status'=> false, 'message'=> 'Giải mã token thất bại', 'data' => []]; }
        Log::info('Notify app get: '. json_encode($token_de));

        $app_key_de = $token_de->appKey ?? null;
        $timestamp = $token_de->timestamp ?? null;
        $barcode = $token_de->barcode ?? null;

        if($app_key_de == null || $timestamp == null || $barcode == null){ return ['status'=> false, 'message'=> 'Giải mã token thất bại', 'data' => []]; }
        if($app_key_de != $app_key){ return ['status'=> false, 'message'=> 'Giải mã token thất bại', 'data' => []]; }

        $diff_time = $this->public_functions->s_datediff('i',date('d-m-y H:i:s'),date('d-m-y H:i:s',$timestamp));
        if ($diff_time > 10) return ['status'=> false, 'message'=> 'Hết thời gian gọi API', 'data' => []];

        $app_notifies =  $this->app_notifies->getAppNotifyByBarcodeForApp($barcode);

        if($app_notifies) return $app_notifies;
    }
}
