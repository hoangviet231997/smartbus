<?php
namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Api1\ApiController;
use App\Models\NotifyType;
use Illuminate\Http\Request;
use App\Services\PublicFunctionService;


class AdminNotifiesApi extends ApiController
{
    protected $request;
    protected $public_functions;
    /**
     * Constructor
     */
    public function __construct(Request $request, PublicFunctionService $public_functions)
    {
        $this->request = $request;
        $this->public_functions = $public_functions;
    }

    /**
     * Operation createNotifyType
     *
     * create.
     *
     *
     * @return Http response
     */
    public function createNotifyType()
    {
        if ($this->request) {

            $name = $this->request['name'];
            $key = $this->request['key'];
            $route_link = $this->request['route_link'] ?? null;
            $url_img = $this->request['url_img'] ?? null;

            // check key
            $check_key = NotifyType::where('key', $key)->first();
            if ($check_key) return response('Key already exists',404); 

            $notify_type = new NotifyType();
            $notify_type->name = $name;
            $notify_type->key = $key;
            $notify_type->route_link = $route_link;
            if ($notify_type->save()) {
                if ($url_img) {
                    $path = "/img/notifies/notify-types/";
                    $notify_type->url_img = $this->public_functions->saveImgBase64($url_img, $notify_type->id, $path, 100, 100);
                    $notify_type->save();
                }
                return $notify_type;
            }
            return response('Create faild', 404);
        }
    }
    /**
     * Operation listNotifyTypes
     *
     * list.
     *
     *
     * @return Http response
     */
    public function listNotifyTypes()
    {
       return NotifyType::all();
    }
    /**
     * Operation updateNotifyType
     *
     * update.
     *
     *
     * @return Http response
     */
    public function updateNotifyType()
    {
        if ($this->request) {

            $name = $this->request['name'];
            $key = $this->request['key'];
            $route_link = $this->request['route_link'] ?? null;
            $url_img = $this->request['url_img'] ?? null;
            $id = $this->request['id'];

            // check key
            $check_key = NotifyType::where('key', $key)->where('id', '!=', $id)->first();
            if ($check_key) return response('Key already exists', 404);

            $notify_type = NotifyType::find($id);
            if ($notify_type) {

                if ($url_img !== null) {
                    $path = "/img/notifies/notify-types/";
                    $this->public_functions->removeImageBase64($notify_type->url_img, $path);
                    $notify_type->url_img = $this->public_functions->saveImgBase64($url_img, $id, $path, 100, 100);
                }

                $notify_type->name = $name;
                $notify_type->key = $key;
                $notify_type->route_link = $route_link;

                if ($notify_type->save()) return $this->getNotifyTypeById($id);
            }
            return response('Update notify not found', 404);
        }
    }
    /**
     * Operation deletenotifyTypes
     *
     * delete.
     *
     * @param int $notify_type_id  (required)
     *
     * @return Http response
     */
    public function deleteNotifyType($notify_type_id)
    {
        $del_notify_type_id = NotifyType::find($notify_type_id);
        if ($del_notify_type_id->delete()) {
            $path = "/img/notifies/notify-types/";
            $this->public_functions->removeImageBase64($del_notify_type_id->url_img, $path);
            return response('Delete OK!', 200);
        } 

    }
    /**
     * Operation getNotifyTypesById
     *
     * Find by ID.
     *
     * @param int $notify_type_id  (required)
     *
     * @return Http response
     */
    public function getNotifyTypeById($notify_type_id)
    {
        return NotifyType::find($notify_type_id);
    }
}
