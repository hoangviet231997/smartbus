<?php

namespace App\Services;

use App\Models\Notify;
use App\Services\PublicFunctionService;

class NotifiesService
{
    protected $public_functions;

    public function __construct(PublicFunctionService $public_function){
       $this->public_functions = $public_function;
    }

    public function getListNotifySharesByCompanyId($company_id = null)
    {
        if(!empty($company_id)){
            $notifies = Notify::join('notify_types', 'notify_types.id', '=' ,'notifies.notify_type_id')
                ->where('notifies.company_id',$company_id)
                ->orderBy('notifies.created_at', 'DESC')
                ->select('notifies.*', 'notify_types.key', 'notify_types.url_img', 'notify_types.route_link')
                ->limit(20)->get();
            return $notifies;
        }else{
            return null;
        }
    }


    public function updateReadedNotify($data)
    {   
        $id = (int)$data['id'] ?? 0;
        $readed = $data['readed'];
        $company_id = (int)$data['company_id'];

        if($id > 0){
            $notifies = Notify::where('id', '=',$id)->update(['readed' => 1]);
            return response("Update OK", 200);
        }else {
            if($company_id > 0) {
                $notifies = Notify::where('company_id', $company_id)->where('readed', '=', 0)->update(['readed' => 1]);
                return response("Update OK", 200);
            }
        }
    }

    public function listNotifies($data)
    {
        $limit = $data['limit'];
        $company_id = $data['company_id'];

        if (empty($limit) && $limit < 0) $limit = 10;

        $pagination = Notify::where('notifies.company_id',$company_id)
            ->join('notify_types', 'notify_types.id', '=' ,'notifies.notify_type_id')
            ->select('notifies.*', 'notify_types.key', 'notify_types.url_img', 'notify_types.route_link')
            ->orderBy('notifies.created_at', 'DESC')
            ->paginate($limit)
            ->toArray();

        header("pagination-total: " . $pagination['total']);
        header("pagination-current: " . $pagination['current_page']);
        header("pagination-last: " . $pagination['last_page']);

        return $pagination['data'];
    }

    public function deleteNotify($id)
    {
        $notify = Notify::find($id);
        if ($notify->delete()) {
            $path = "/img/notifies/notify-types/";
            $this->public_functions->removeImageBase64($notify->url_img, $path);
            return response('Delete notify OK!', 200);

        }
    }

    public function listNotifyByInputAndByTypeSearch($data)
    {
        $key_input = $data['key_input'] ?? '';
        $style_search = $data['style_search'] ?? '';
        $company_id = $data['company_id'];

        $notify = Notify::join('notify_types', 'notify_types.id', '=', 'notifies.notify_type_id')
            ->where('notifies.company_id',$company_id)
            ->select('notifies.*', 'notify_types.key', 'notify_types.url_img', 'notify_types.route_link')
            ->orderBy('notifies.created_at', 'DESC');

        if ($style_search == 'title') {
            $notify->where('notifies.title', 'like', "%$key_input%");
        }

        else if ($style_search == 'date') {
            $date_from = $key_input[0] ?? '';
            $date_to = $key_input[1] ?? '';
            $notify->where([['notifies.created_at', '>=',date('Y-m-d 00:00:00', strtotime($date_from))], ['notifies.created_at', '<=', date('Y-m-d 23:59:59', strtotime($date_to))]]);
        }

        else if ($style_search == 'type') {
            $notify->where('notify_types.key', '=', $key_input);
        }

        return $notify->get()->toArray();
    }
}
