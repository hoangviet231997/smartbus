<?php
namespace App\Services; 

use App\Models\GroupKey;
use App\Models\Company;
use App\Models\CompanyGroupKey;
use App\Services\PushLogsService;
use Illuminate\Support\Facades\Hash;

class GroupKeyCompaniesService{
    
     /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;


    public function __construct(
         PushLogsService $push_logs  
    )
    {
        $this->push_logs = $push_logs;
    }

    public function checkExistsByKey($key)
    {
        return GroupKey::where('key', $key)->exists();
    }

    public function createGroupKeyCompanies($data){

        $name = $data['name'];
        $companies = $data['companies'];
        $type = $data['type'] ?? '';

        //create group key
        $group_key = new GroupKey();
        $group_key->name = $name;
        $group_key->type = $type;
        //set params key 

        $key_flag = false;
        $key_set = '';
        while(!$key_flag) {
            $key_set = base64_encode(md5(uniqid()));
            if (!$this->checkExistsByKey($key_set)) { $key_flag = true; }
        }
        $group_key->key = $key_set;

        if($group_key->save()){
            if(count($companies)> 0){
                foreach ($companies as $value) {    
                    //create company group key
                    $company_group_key = new CompanyGroupKey();
                    $company_group_key->company_id = $value;
                    $company_group_key->key = $group_key->key;
                    if ($company_group_key->save()) {
                        //create Push Log for Group Company
                        $push_log = [];
                        $push_log['action'] = 'create';
                        $push_log['company_id'] = $company_group_key->company_id;
                        $push_log['subject_id'] = $company_group_key->id;
                        $push_log['subject_type'] = 'group_company';
                        $push_log['subject_data'] = $company_group_key;
                        $this->push_logs->createPushLog($push_log);
                    }
                }
            }
            return $group_key;
        }
    }
    
    public function listGroupKeyCompanies(){
        return GroupKey::all()->toArray();
    }

    public function getGroupKeyCompaniesById($id){

        //return group by id
        $group_key =  GroupKey::find($id);
        
        $result = array(
            'id' =>  $group_key->id,
            'name' => $group_key->name,
            'type' => $group_key->type,
            'key' => $group_key->key,
            'companies' => [],
            'companies_tmp' => []
        );

        if(!empty( $group_key)){

            $group_key_companies = CompanyGroupKey::where('key',$group_key->key)->pluck('company_id')->toArray();
            $tmp_arr = CompanyGroupKey::where('key','!=',$group_key->key)->pluck('company_id')->toArray();

            //khong goi service cua companies boi vi error bug cua laravel khi provider khoi tao doi tuong => goi model
            $companie_arr =  Company::whereNotIn('id',$tmp_arr)->orderBy('name')->get()->toArray();
            
            if(count( $group_key_companies) > 0){
                $result['companies']  = $group_key_companies;
                $result['companies_tmp']  = $companie_arr;
            }
        }
        return $result;
    }

	public function updateGroupKeyCompanies($data)
    {
        //return group by id
        $group_key =  GroupKey::find($data['id']);
        if(empty($group_key)) return response('Group company not found',404);

        // update
        $group_key->name = $data['name'];
        $group_key->type = $data['type'] ?? '';
        $group_key->key = $group_key->key;
        $companies = $data['companies'];

        if($group_key->save()){
            $group_key_companies = CompanyGroupKey::where('key',$group_key->key)->get();

            if (count($companies) > 0) {
                foreach ($group_key_companies as $vl){

                    if(in_array($vl->company_id,$companies)) {
                        $this->deleteElement($vl->company_id, $companies);    
                    }else {
                        //create Push Log for Group Company
                        $company_gr = CompanyGroupKey::where('company_id',$vl->company_id)->first();
                        if($company_gr){
                            if($company_gr->delete()){
                                $push_log = [];
                                $push_log['action'] = 'delete';
                                $push_log['company_id'] = $vl->company_id;
                                $push_log['subject_id'] = $vl->id;
                                $push_log['subject_type'] = 'group_company';
                                $push_log['subject_data'] = null;
                                $this->push_logs->createPushLog($push_log);
                            }
                        }
                    } 
                }
                // return $companies;
                if(count($companies) > 0){
                    foreach ($companies as $value) 
                    {
                        // create company group key
                        $company_gr = new CompanyGroupKey();
                        $company_gr->company_id = $value;
                        $company_gr->key = $group_key->key;

                        if ($company_gr->save()) {                           
                            //create Push Log for Group Company    
                            $push_log = [];
                            $push_log['action'] = 'create';
                            $push_log['company_id'] = $company_gr->company_id;
                            $push_log['subject_id'] = $company_gr->id;
                            $push_log['subject_type'] = 'group_company';
                            $push_log['subject_data'] = $company_gr;
                            $this->push_logs->createPushLog($push_log);                      
                        }
                    }
                }  
            }else{
                // delete CompanyGroupKey
                if(count($group_key_companies) > 0){
                    foreach($group_key_companies as $vl){
                        if ($vl->delete()) {
                            //create Push Log for Group Company
                            $push_log = [];
                            $push_log['action'] = 'delete';
                            $push_log['company_id'] = $vl->company_id;
                            $push_log['subject_id'] = $vl->id;
                            $push_log['subject_type'] = 'group_company';
                            $push_log['subject_data'] = null;
                            $this->push_logs->createPushLog($push_log);
                        }                        
                    }
                }
            }
            return $this->getGroupKeyCompaniesById($data['id']);  
        }
    }

    public function deleteElement($element, &$array){
        $index = array_search($element, $array);
        if($index !== false){
            unset($array[$index]);
        }
    }

    public function deleteGroupKeyCompanies ($id){
        //return group by id
        $group_key =  GroupKey::find($id);

        if (empty($group_key)) return response('Not found', 404);

        if ($group_key->delete()) 
        {
            $group_key_companies = CompanyGroupKey::where('key',$group_key->key)->get();

            if (count($group_key_companies) > 0) {
                foreach ($group_key_companies as $company_gr) {
                    if ($company_gr->delete()) {
                        //create Push Log for Group Company
                        $push_log = [];
                        $push_log['action'] = 'delete';
                        $push_log['company_id'] = $company_gr->company_id;
                        $push_log['subject_id'] = $company_gr->id;
                        $push_log['subject_type'] = 'group_company';
                        $push_log['subject_data'] = null; 
                        $this->push_logs->createPushLog($push_log);
                    }   
                }
            }
            return response('OK', 200);
        }
        return response('Delete Error', 404);
    }

    public function getCompanyGroupArrayId(){
        return CompanyGroupKey::pluck('company_id')->toArray();
    }

    public function getGroupKeyCompaniesByKeyAndCompanyId($key,$company_id){
        
        return CompanyGroupKey::where('key',$key)->where('company_id', $company_id)->first();
    }

    public function listGroupKeyCompaniesByTypeForApp($type)
    {
        if($type == 1){
            
            $group_companies =  CompanyGroupKey::join('group_key','group_key.key', '=', 'company_group_key.key')
                            ->join('companies','companies.id', '=', 'company_group_key.company_id')
                            ->where('group_key.type','=','group_company_mbs_register')
                            ->select('companies.id', 'companies.name', 'companies.fullname', 'companies.address')
                            ->get();

            if (count($group_companies) > 0) return ['status'=> true, 'message'=> 'Thành công', 'data' => $group_companies];
            
            return ['status'=> false, 'message'=> 'Không có dữ liệu', 'data' => []];
        }
        return ['status'=> false, 'message'=> 'Tham số đường truyền không hợp lệ', 'data' => []];
    }
}