<?php
namespace App\Services;
use App\Services\PushLogsService;

class SettingGlobalsService
{

    /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;

    public function __construct(PushLogsService $push_logs)
    {
        $this->push_logs = $push_logs;
    }


    public function vn_str_filter ($str){
        $str = strtolower($str);
        $unicode = array(

            'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',

            'd'=>'đ',

            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',

            'i'=>'í|ì|ỉ|ĩ|ị',

            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',

            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',

            'y'=>'ý|ỳ|ỷ|ỹ|ỵ'
        );
        $chars = array('~','^','&','*','_','__','`','@','#','$','%','!',' !',' ?','?','"','“','”',':','+','/','(',')','--','.',',','=','™');

        foreach($unicode as $nonUnicode=>$uni){
                $str = preg_replace("/($uni)/i", $nonUnicode, trim($str));
            $str = trim(str_replace($chars,' ',$str));
                $str = str_replace(' ','_',$str);

        }
        return $str;
   }

    public  function in_array_field($needle, $needle_field, $haystack, $strict = false) {
        if ($strict) {
            foreach ($haystack as $item)
                if (isset($item[$needle_field]) && $item[$needle_field] === $needle)
                    return true;
        }
        else {
            foreach ($haystack as $item)
                if (isset($item[$needle_field]) && $item[$needle_field] == $needle)
                    return true;
        }
        return false;
    }

    public function listSettingGlobals($data){

        $company_id = $data['company_id'];
        $path = public_path() . "/file/setting-global.json";
        //get file
        $setting_globals = [];

        //check exist file
        if (file_exists($path)){
            $results = json_decode(file_get_contents($path), true);
            if ($results) {
                foreach($results as $key => $value) {
                    if((int)$key == $company_id){
                        if(count($value) > 0){
                            $tmp = [];
                            foreach($value as $v){

                                $obj_tmp = new \stdClass;
                                $obj_tmp->key = $v['key'];
                                $obj_tmp->value = $v['value'];
                                array_push($tmp,$obj_tmp);
                            }
                            $setting_globals = $tmp;
                        }
                    }
                }
            }
        }

        return $setting_globals;
    }

    public function createSettingGlobal($data){

        $company_id = $data['company_id'];
        $key_params = $this->vn_str_filter($data['key']);
        $value_params = $data['value'];
        $path = public_path() . "/file/setting-global.json";
        //get file
        $setting_globals = [];

        //check exist file
        if (file_exists($path)){

            $results = json_decode(file_get_contents($path), true);
            $results  = (array)$results;

            if ($results) {

                if(array_key_exists($company_id, $results)){

                    if($this->in_array_field($key_params, 'key', $results[$company_id])){
                        return response('Key already exists', 404);
                    }

                    //handle company exist
                    $tmp_request = new \stdClass;
                    $tmp_request->key = $key_params;
                    $tmp_request->value = $value_params;
                    array_push($results[$company_id],$tmp_request);
                    $setting_globals[(string)$company_id] = $results[$company_id];

                    //handle company in array
                    unset($results[$company_id]);
                    foreach ($results as $key => $values) {
                        $setting_globals[(string)$key] = $values;
                    }
                }else{

                    //handle company in array
                    foreach ($results as $key => $values) {
                        $setting_globals[(string)$key] = $values;
                    }

                    //handle company new
                    $tmp_company = [];
                    $tmp_request = new \stdClass;
                    $tmp_request->key =  $key_params;
                    $tmp_request->value =  $value_params;
                    $tmp_company[] = $tmp_request;
                    $setting_globals[(string)$company_id] = $tmp_company;
                }

            }else{
                //handle company new
                $tmp_company = [];
                $tmp_request = new \stdClass;
                $tmp_request->key =  $key_params;
                $tmp_request->value =  $value_params;
                $tmp_company[] = $tmp_request;
                $setting_globals[(string)$company_id] = $tmp_company;
            }
        }

        if(file_put_contents($path, json_encode($setting_globals))) {

            $where = [
                ['company_id', $company_id],
                ['action', 'create'],
                ['subject_type', 'setting_global']
            ];
            $check_push_log = $this->push_logs->getPushLogByOptions($where);

            $push_log_v = [];
            $push_log_v['action'] = 'create';
            $push_log_v['company_id'] =  $company_id;
            $push_log_v['subject_id'] = null ;
            $push_log_v['subject_type'] = 'setting_global';

            if(count( $check_push_log ) > 0){
                foreach($check_push_log as $vl){
                    if($vl->delete()){
                        $push_log_v['subject_data'] =  $setting_globals[$company_id];
                    }
                }
            }else{
                $push_log_v['subject_data'] =  $setting_globals[$company_id];
            }
            $this->push_logs->createPushLog($push_log_v);
        }
    }

    public function deleteSettingGlobal($data){

        //get file
        $company_id = $data['company_id'];
        $key_params = $data['key'];
        $value_params = $data['value'];
        $path = public_path() . "/file/setting-global.json";
        //get file
        $setting_globals = [];

        //check exist file
        if (file_exists($path)){

            $results = json_decode(file_get_contents($path), true);

            if ($results) {

                if(array_key_exists($company_id, $results)){

                    //handle key resquest in array
                    $tmp = [];
                    foreach($results[$company_id] as $v){

                        if(($v['key'] != $key_params) || ($v['value'] != $value_params)){
                            $obj_tmp = new \stdClass;
                            $obj_tmp->key = $v['key'];
                            $obj_tmp->value = $v['value'];
                            array_push($tmp,$obj_tmp);
                        }
                    }
                    $setting_globals[(string)$company_id] = $tmp;
                    unset($results[$company_id]);

                    //hadle company orther
                    foreach ($results as $key => $values) {
                        $setting_globals[(string)$key] = $values;
                    }
                }
            }
        }

        if(file_put_contents($path, json_encode($setting_globals))) {

            $where = [
                ['company_id', $company_id],
                ['action', 'create'],
                ['subject_type', 'setting_global']
            ];
            $check_push_log = $this->push_logs->getPushLogByOptions($where);

            $push_log_v = [];
            $push_log_v['action'] = 'create';
            $push_log_v['company_id'] =  $company_id;
            $push_log_v['subject_id'] = null ;
            $push_log_v['subject_type'] = 'setting_global';

            if(count( $check_push_log ) > 0){
                foreach($check_push_log as $vl){
                    if($vl->delete()){
                        $push_log_v['subject_data'] =  $setting_globals[$company_id];
                    }
                }
            }else{
                $push_log_v['subject_data'] =  $setting_globals[$company_id];
            }
            $this->push_logs->createPushLog($push_log_v);
            return response('Delete OK', 200);
        }
    }

    public function getValueSettingGlobalByKey($str_path, $company_id, $key){
      if($str_path && $company_id && $key){
        $path = public_path().$str_path;
        if (file_exists($path)){
          $results = json_decode(file_get_contents($path), true);
          if ($results) {
            if(array_key_exists($company_id, $results)){
                $result = null;
                foreach($results[$company_id] as $v){
                    if($v['key'] == $key) {
                      $result = (int)$v['value'];
                      break;
                    }
                }
                return $result;
            }
          }
        }
      }
    }
}
