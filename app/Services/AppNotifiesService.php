<?php

namespace App\Services;

use App\Models\AppNotify;
use App\Services\PublicFunctionService;
use App\Services\RfidCardsService;
use App\Services\MembershipsService;
use Log;

class AppNotifiesService
{

    protected $public_function;

    /**
     * @var App\Services\RfidCardsService
     */
    protected $rfidcards;

    /**
     * @var App\Services\MembershipsService
     */
    protected $memberships;


    public function __construct(
      PublicFunctionService $public_function,
      RfidCardsService $rfidcards,
      MembershipsService $memberships
    ){
        $this->public_function = $public_function;
        $this->rfidcards = $rfidcards;
        $this->memberships = $memberships;
    }

    public function createAppNotify($data)
    {
        if ($data) {

            $name = $data['name'];
            $description = $data['description'];
            $url_img = $data['url_img'] ?? null;
            $content = $data['content'] ?? null;
            $weigth = $data['weigth'] ?? 0;
            $company_id = $data['company_id'] ?? 0;

            $app_notify = new AppNotify();
            $app_notify->name = $name;
            $app_notify->description = $description;
            $app_notify->content = $content;
            $app_notify->weigth = $weigth;
            $app_notify->company_id = $company_id;

            if($url_img){
              $path = "/img/notifies/app-notify/";
              $app_notify->url_img = $this->public_function->saveImgBase64($url_img, $company_id, $path, 100, 100);
            }

            if ($app_notify->save())   return $app_notify;

            return response('Create faild', 401);
        }
    }

    public function listAppNotify($data)
    {

        $limit = $data['limit'];
        $company_id = $data['company_id'];

        if(empty($limit) && $limit < 0) $limit = 10;

        $pagination = AppNotify::where('company_id', $company_id)
            ->orderBy('created_at', 'desc')
            ->paginate($limit)
            ->toArray();

        header("pagination-total: " . $pagination['total']);
        header("pagination-current: " . $pagination['current_page']);
        header("pagination-last: " . $pagination['last_page']);

        return $pagination['data'];
    }

    public function updateAppNotify($data)
    {
        if ($data) {
            $name = $data['name'] ?? '';
            $url_img = $data['url_img'] ?? null;
            $description = $data['description'] ?? '';
            $content = $data['content'] ?? null;
            $weigth = $data['weigth'] ?? 0;
            $company_id = $data['company_id'] ?? 0;
            $id = $data['id'] ?? 0;

            $app_notify = AppNotify::find($id);
            if ($app_notify) {

                if ($url_img !== null) {
                    $path = "/img/notifies/app-notify/";
                    $this->public_function->removeImageBase64($app_notify->url_img, $path);
                    $app_notify->url_img = $this->public_function->saveImgBase64($url_img, $company_id, $path, 100, 100);
                }

                $app_notify->name = $name;
                $app_notify->description = $description;
                $app_notify->content = $content;
                $app_notify->weigth = $weigth;

                if ($app_notify->save()) return $this->getAppNotifyById($id);
            }
            return response('Update notify not found', 404);
        }
    }

    public function searchAppNotifyByInput($data)
    {
        $company_id = $data['company_id'];
        $key_input = $data['key_input'];
        $style_search = $data['style_search'];

        $app_notifies = AppNotify::where('company_id', $company_id)->orderBy('created_at', 'desc');

        if ($style_search == 'name') $app_notifies->where('name', 'like', "%$key_input%");

        return $app_notifies->get()->toArray();
    }

    public function getAppNotifyById($app_notify_id)
    {
        return AppNotify::find($app_notify_id);
    }

    public function deleteAppNotify($app_notify_id)
    {
        $app_notify = AppNotify::find($app_notify_id);
        if ($app_notify->delete()) {
            $path = "/img/notifies/app-notify/";
            $this->public_function->removeImageBase64($app_notify->url_img, $path);
            return response('Delete News OK!', 200);
        }
    }

    public function getAppNotifyByBarcodeForApp($barcode)
    {

        if($barcode != null){

            $rfidcard = $this->rfidcards->getDataByBarcode($barcode);

            Log::info('Notify app get Barcode: '.json_encode($rfidcard));

            if($rfidcard){

                $membership = $this->memberships->getMembershipByRfidcardIdForApp($rfidcard->id);

                Log::info('Notify app get MBS: '.json_encode($membership));

                if($membership){

                  $app_notifies = AppNotify::where('company_id',$membership->company_id)->orderBy('created_at', 'desc')->limit(20)->get();

                  if (count($app_notifies) > 0) return ['status' => true, 'message' => 'Thành công', 'data' => $app_notifies ];

                  return  ['status' => false, 'message' => 'Không có dữ liệu', 'data' => [] ];
                }
                return ['status' => false, 'message' => 'Thẻ thành viên không tồn tại', 'data' => []];
            }
            return ['status' => false, 'message' => 'Thẻ thành viên không tồn tại', 'data' => []];
        }
    }
}
