<?php
namespace App\Services;

use App\Models\Application;
use App\Services\PushLogsService;
use App\Services\TransactionsService;

class AppsService
{
    /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;

    /**
     * @var App\Services\TransactionsService
     */
    protected $transactions;

    public function __construct(PushLogsService $push_logs, TransactionsService $transactions)
    {
        $this->push_logs = $push_logs;
        $this->transactions = $transactions;
    }

    public function getAppsByApiKey($api_key)
    {
        return Application::where('api_key', $api_key)->first();
    }

    public function insertTicket($data, $api_key)
    {   
        // get app
        $app = $this->getAppsByApiKey($api_key);

        if (!$app) {
            return response('App key is invalid', 404);
        }

        $data['type'] = 'app:'.$app->id;
        $data['duration'] = $data['duration'] ?? 24;
        $data['company_id'] = $app->company_id;
        $data['timestamp'] = empty($data['timestamp']) 
                                ? date("Y-m-d H:i:s")
                                : date("Y-m-d H:i:s", $data['timestamp']);
        // check
        $is_valid = $this->transactions->getTransactionByOptions([
                        ['ticket_number', $data['ticket_code']]
                    ]);                        

        if (count($is_valid) > 0) {
            return response('Ticket code already exists', 404);
        }

        // save transaction
        $transaction = $this->transactions->createTransactionForApp($data);

        if ($transaction) {
            return response('OK, created', 200);
        }
        return response('Create error', 404);
    }

    public function getTicketInfo($ticket_code, $api_key)
    {
        // get app
        $app = $this->getAppsByApiKey($api_key);

        if (!$app) {
            return response('App key is invalid', 404);
        }

        // get transaction by ticket code
        $transactions = $this->transactions->getTransactionByOptions([
                            ['ticket_number', $ticket_code],
                            ['company_id', $app->company_id],
                            ['type', 'like', 'app%']
                        ]);

        if (count($transactions) <= 0) {
            return response('Data not found', 404);
        }

        $transaction = $transactions[0];
        $data = [];
        $data['duration'] = $transaction->duration / 3600;
        $data['ticket_code'] = $transaction->ticket_number;
        $data['price'] = $transaction->amount;
        $data['timestamp'] = strtotime($transaction->created_at);

        // get push_log
        $push_logs = $this->push_logs->getPushLogByOptions([
                        ['subject_id', $transaction->id],
                        ['subject_type', 'voucher']
                    ]);

        if (count($push_logs) > 0) {
            $push_log = $push_logs[0];

            $subject_data = json_decode($push_log->subject_data);
            $data['startdate'] = null;

            if (!empty($subject_data->startdate)) {
                $data['startdate'] = date("Y-m-d", $subject_data->startdate);
            }

            $data['moreinfo'] = $subject_data->moreinfo;
        }

        return $data;
    }

    public function listApps($data)
    {
        $limit = $data['limit'];
        $company_id = $data['company_id'];

        if (empty($limit) && $limit < 0) 
            $limit = 10;

        $pagination = Application::where('company_id', $company_id)
                        ->orderBy('created_at', 'desc')
                        ->paginate($limit)
                        ->toArray();

        header("pagination-total: ".$pagination['total']);
        header("pagination-current: ".$pagination['current_page']);
        header("pagination-last: ".$pagination['last_page']);

        return $pagination['data'];        
    }

    public function createApp($data)
    {
        $company_id = $data['company_id'];
        $company_name = $data['company_name'];
        $company_address = $data['company_address'] ?? null;
        $email = $data['email'] ?? null;
        $url = $data['url'] ?? null;
        $api_key = base64_encode(md5(uniqid()));

        // save
        $app = new Application();
        $app->company_name = $company_name;
        $app->company_address = $company_address;
        $app->company_id = $company_id;
        $app->api_key = $api_key;
        $app->url = $url;
        $app->email = $email;

        if ($app->save()) {
            return $this->getAppById($app['id']);
        }
        return response('Create Error', 404);
    }

    public function updateApp($data)
    {
        $id = $data['id'];
        $company_id = $data['company_id'];
        $company_name = $data['company_name'];
        $company_address = $data['company_address'] ?? null;
        $email = $data['email'] ?? null;
        $url = $data['url'] ?? null;

        // get app by id
        $app = $this->getAppById($id);

        if (empty($app))
            return response('Application not found', 404);

        $app->company_name = $company_name;
        $app->company_address = $company_address;
        $app->company_id = $company_id;
        $app->url = $url;
        $app->email = $email;

        if ($app->save()) {
            return $this->getAppById($app->id);
        }
        return response('Update Error', 404);
    }

    public function deleteApp($id)
    {
        // get Vehicle
        $app = $this->getAppById($id);

        if (empty($app)) 
            return response('Application not found', 404);

        if ($app->delete()) 
            return response('OK', 200);
        
        return response('Delete Error', 404);        
    }

    public function getAppById($id, $company_id = null)
    {
        if (empty($company_id)) {

            return Application::where('id', $id)->first();
        } else {

            return Application::where('id', $id)
                        ->where('company_id', $company_id)
                        ->first();
        }
    }

    public function changeApiKeyById($id, $company_id)
    {
        $app = $this->getAppById($id, $company_id);

        if (empty($app)) {
            return response('Application not found', 404);
        }

        $app->api_key = base64_encode(md5(uniqid()));

        if ($app->save()) {
            return $this->getAppById($app->id);
        }

        return response('Change api key error', 404);
    }
}