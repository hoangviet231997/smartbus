<?php
namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\NotifiesService;

class ManagerNotifiesApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\NotifiesService
     */
    protected $notifies;

    /**
     * Constructor
     */
    public function __construct(Request $request, NotifiesService $notifies)
    {
        $this->request = $request;
        $this->notifies = $notifies;
    }

    /**
     * Operation managerListNotifies
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerListNotifies()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->notifies->listNotifies($input);
    }
    /**
     * Operation managerListNotifyShares
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerListNotifyShares()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return $this->notifies->getListNotifySharesByCompanyId($user->company_id);
    }
    /**
     * Operation managerDeleteNotify
     *
     * delete.
     *
     * @param int $notify_id  (required)
     *
     * @return Http response
     */
    public function managerDeleteNotify($notify_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return $this->notifies->deleteNotify($notify_id);

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
    public function managerGetNotifyById($notify_id)
    {
    }

     /**
     * Operation managerUpdateReadedNotify
     *
     * update read notify.
     *
     *
     * @return Http response
     */
    public function managerUpdateReadedNotify()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();    
        $input['company_id'] = $user->company_id;

        return $this->notifies->updateReadedNotify($input);
    }

    public function managerListNotifyByInputAndByTypeSearch()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();    
        $input['company_id'] = $user->company_id;

        return $this->notifies->listNotifyByInputAndByTypeSearch($input);
    }
}
