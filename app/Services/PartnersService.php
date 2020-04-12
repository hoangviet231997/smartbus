<?php
namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerAccount;
use App\Services\PublicFunctionService;
use App\Services\PushLogsService;

class PartnersService
{
    /**
     * @var App\Services\PublicFunctionService
     */
    protected $public_functions;

    /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;

    public function __construct(PublicFunctionService $public_functions, PushLogsService $push_logs)
    {
        $this->public_functions = $public_functions;
        $this->push_logs = $push_logs;
    }

    public function checkExistsById($id)
    {
        return Partner::where('id', $id)->exists();
    }

    public function createPartner($data)
    {
        $partners = Partner::all()->toArray();
        $count_partner = count($partners) + 1;
        $partner_code = $data['partner_code'].''.$count_partner;
        $timestamp = ''.time();
        $app_key = $this->public_functions->enCrypto( $timestamp, $partner_code);

        $partner = new Partner;
        $partner->company_name = $data['company_name'] ?? null;
        $partner->partner_code = $partner_code;
        $partner->app_key = $app_key;
        $partner->company_fullname = $data['company_fullname'] ?? null;
        $partner->address = $data['address'] ?? null;
        $partner->url = $data['url'] ?? null;
        $partner->phone = $data['phone'] ?? null;
        $partner->email = $data['email'] ?? null;
        $partner->group_company = count($data['group_company']) > 0 ? json_encode($data['group_company']) : null;

        if ($partner->save()) {
            return $partner;
        }

        return response('Create Error', 404);
    }

    public function updatePartner($data)
    {
        $is_check = $data['is_check'];

        $partner = Partner::find($data['id']);

        if (empty($partner))
            return response('Partner Not found', 404);

        $partner->company_name = $data['company_name'] ?? null;
        $partner->company_fullname = $data['company_fullname'] ?? null;
        $partner->address = $data['address'] ?? null;
        $partner->url = $data['url'] ?? null;
        $partner->phone = $data['phone'] ?? null;
        $partner->email = $data['email'] ?? null;
        $partner->group_company = count($data['group_company']) > 0 ? json_encode($data['group_company']) : null;

        if($is_check == 1){
            $timestamp = ''.time();
            $app_key = $this->public_functions->enCrypto( $timestamp,  $partner->partner_code);
            $partner->app_key = $app_key;
        }

        if ($partner->save())
            return $partner;

        return response('Update Error', 404);
    }

    public function getPartnerById($id)
    {
        return Partner::find($id);
    }

    public function checkExistPartnerByPartnerCode($partner_code)
    {
        return Partner::where("partner_code", $partner_code)->exists();
    }

    public function getPartnerByPartnerCode($partner_code)
    {
        return Partner::where("partner_code", $partner_code)->first();
    }

    public function deletePartner($id)
    {
        // get Partner
        $partner = Partner::find($id);

        if (empty($partner)) return response('Partner Not found', 404);

        if ($partner->delete()) {

            return response('OK', 200);
        }

        return response('Delete Error', 404);
    }

    public function listPartners()
    {
        return Partner::all()->toArray();
    }

    //func partner account
    public function createPartnerAccount($data)
    {

        $partner_account = new PartnerAccount;
        $partner_account->company_id = $data['company_id'];
        $partner_account->name = $data['name'];
        $partner_account->partner_code = $data['partner_code'];
        $partner_account->url_api = $data['url_api'];
        $partner_account->username_login = $data['username_login'];
        $partner_account->password_login = $data['password_login'];
        $partner_account->public_key = $data['public_key'];
        $partner_account->private_key = $data['private_key'] ?? null;
        $partner_account->description = $data['description'] ?? null;

        if ($partner_account->save()) {

            $partner_account = $partner_account->toArray();
            unset($partner_account['username_login']);
            unset($partner_account['password_login']);
            unset($partner_account['description']);
            unset($partner_account['updated_at']);
            unset($partner_account['created_at']);

            $push_log = [];
            $push_log['action'] = 'create';
            $push_log['company_id'] = $partner_account['company_id'];
            $push_log['subject_id'] = $partner_account['id'];
            $push_log['subject_type'] = 'partner_account';
            $push_log['subject_data'] = $partner_account;
            $this->push_logs->createPushLog($push_log);

            return $partner_account;
        }

        return response('Create Error', 404);
    }

    public function listPartnerAccounts()
    {
        return PartnerAccount::with('company')->get();
    }

    public function updatePartnerAccount($data)
    {
        $partner_account = PartnerAccount::find((int)$data['id']);

        if (empty($partner_account)) return response('Partner Account Not found', 404);

        $partner_account->company_id = $data['company_id'];
        $partner_account->name = $data['name'];
        $partner_account->partner_code = $data['partner_code'];
        $partner_account->url_api = $data['url_api'];
        $partner_account->username_login = $data['username_login'];
        $partner_account->password_login = $data['password_login'];
        $partner_account->public_key = $data['public_key'];
        $partner_account->private_key = $data['private_key'] ?? null;
        $partner_account->description = $data['description'] ?? null;

        if ($partner_account->save()) {

            $partner_account = $partner_account->toArray();
            unset($partner_account['username_login']);
            unset($partner_account['password_login']);
            unset($partner_account['description']);
            unset($partner_account['updated_at']);
            unset($partner_account['created_at']);

            $push_log = [];
            $push_log['action'] = 'update';
            $push_log['company_id'] = $partner_account['company_id'];
            $push_log['subject_id'] = $partner_account['id'];
            $push_log['subject_type'] = 'partner_account';
            $push_log['subject_data'] = $partner_account;
            $this->push_logs->createPushLog($push_log);

            return $partner_account;
        }

        return response('Update Error', 404);
    }

    public function deletePartnerAccount($partner_account_id)
    {
        // get partner account
        $partner_account = PartnerAccount::find($partner_account_id);

        if (empty($partner_account)) return response('Partner Account Not found', 404);

        $company_id = $partner_account->company_id;
        $partner_account_id = $partner_account->id;

        if ($partner_account->delete()) {

            //  create log
            $push_log = [];
            $push_log['action'] = 'delete';
            $push_log['company_id'] = $company_id;
            $push_log['subject_id'] = $partner_account_id;
            $push_log['subject_type'] = 'partner_account';
            $push_log['subject_data'] = null;
            $this->push_logs->createPushLog($push_log);

            return response('OK', 200);
        }

        return response('Delete Error', 404);
    }

    public function getPartnerAccountById($partner_account_id)
    {
        return PartnerAccount::where('id', $partner_account_id)->with('company')->first();
    }
    //end func partner account
}
