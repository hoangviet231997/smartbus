<?php
namespace App\Services;

use App\Models\Company;
use App\Models\User;
use App\Models\Role;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Support\Facades\Hash;
use App\Services\PushLogsService;
use App\Services\UsersService;
use App\Services\GroupKeyCompaniesService;
use Intervention\Image\ImageManagerStatic as Image;
use phpDocumentor\Reflection\Types\Null_;

class CompaniesService
{
    /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;

    /**
     * @var App\Services\UsersService
     */
    protected $users;

     /**
     * @var App\Services\GroupKeyCompaniesService
     */
    protected $company_group;


    public function __construct(PushLogsService $push_logs, UsersService $users, GroupKeyCompaniesService $company_group)
    {
        $this->push_logs = $push_logs;
        $this->users = $users;
        $this->company_group = $company_group;
    }

    public function saveImgBase64($data, $middle){

        if($data){
            $image = explode(';', $data);

            //get file name
            $img_name = explode('/',  $image[0] );
            $file_name = $middle.'_'.time().'.'.$img_name[1];

            $path = public_path()."/img/layout-card-comapies/".$file_name;

            $img = Image::make(file_get_contents($data));
            $img->resize(1004, 638);
            if( $img->save($path)){
                return $file_name;
            }
        }
    }

    public function removeImageBase64($file_name){

        if($file_name){
            $path = public_path()."/img/layout-card-comapies/".$file_name;
            if(file_exists($path)){
                if(unlink($path)) return true;
            }
        }
    }

    public function checkExistsById($id)
    {
        return Company::where('id', $id)->exists();
    }

    public function createCompany($data)
    {
        // check username exist
        if ($this->users->checkExistsByKey('username', $data['username']))
            return response('User already exists', 404);

        $company = new Company;
        $company->name = $data['name'];
        $company->subname = $data['subname'] ?? null;
        $company->fullname = $data['fullname'];
        $company->address = $data['address'];
        $company->print_at = $data['print_at'] ?? null;
        $company->tax_code = $data['tax_code'] ?? null;
        $company->phone = $data['phone'];
        $company->email = $data['email'];
        $company->position = new Point($data['lat'], $data['lng']);// (lat, lng)

        if ($company->save()) {

            // create Push Log for company
            $push_log = [];
            $push_log['action'] = 'create';
            $push_log['company_id'] = $company['id'];
            $push_log['subject_id'] = $company['id'];
            $push_log['subject_type'] = 'company';
            $push_log['subject_data'] = $company;
            $this->push_logs->createPushLog($push_log);

            // get role manager
            $role = Role::where('name', 'manager')->first();

            // create user manager for company
            $user = new User();
            $user->role_id = $role->id;
            $user->company_id = $company['id'];
            $user->username = $data['username'];
            $user->password = Hash::make($data['password']);
            $user->save();

            // create Push Log for user
            $push_log = [];
            $push_log['action'] = 'create';
            $push_log['company_id'] = $company['id'];
            $push_log['subject_id'] = $user['id'];
            $push_log['subject_type'] = 'user';
            $push_log['subject_data'] = $user;
            $this->push_logs->createPushLog($push_log);

            return $this->getCompanyById($company['id']);
        }

        return response('Create Error', 404);
    }

    public function updateCompany($data)
    {
        $company = Company::find($data['id']);

        if (empty($company))
            return response('Company Not found', 404);

        $company->name = $data['name'];
        $company->fullname = $data['fullname'];
        $company->subname = $data['subname'] ?? null;
        $company->address = $data['address'];
        $company->print_at = $data['print_at'] ?? null;
        $company->tax_code = $data['tax_code'] ?? null;
        $company->phone = $data['phone'];
        $company->email = $data['email'];
        $company->position = new Point($data['lat'], $data['lng']);// (lat, lng)

        if ($company->save()) {

            // create Push Log for company
            $push_log = [];
            $push_log['action'] = 'update';
            $push_log['company_id'] = $company->id;
            $push_log['subject_id'] = $company->id;
            $push_log['subject_type'] = 'company';
            $push_log['subject_data'] = $company;
            $this->push_logs->createPushLog($push_log);

            return $this->getCompanyById($company->id);
        }

        return response('Update Error', 404);
    }

    public function getCompanyById($id)
    {
        $company = Company::find($id);

        if ($company->position) {
            $company->lat = $company->position->getLat();
            $company->lng = $company->position->getLng();
        }

        return $company;
    }

    public function deleteCompany($id)
    {
        // get Company
        $company = Company::find($id);

        if (empty($company)) return response('Company Not found', 404);

        if ($company->delete()) {

            // create Push Log for company
            $push_log = [];
            $push_log['action'] = 'delete';
            $push_log['company_id'] = $id;
            $push_log['subject_id'] = $id;
            $push_log['subject_type'] = 'company';
            $push_log['subject_data'] = null;
            $this->push_logs->createPushLog($push_log);

            return response('OK', 200);
        }

        return response('Delete Error', 404);
    }

    public function listCompanies($data)
    {
        $limit = $data['limit'];
        if (empty($limit) && $limit < 0) $limit = 10;

        $pagination = Company::paginate($limit)->toArray();

        header("pagination-total: " . $pagination['total']);
        header("pagination-current: " . $pagination['current_page']);
        header("pagination-last: " . $pagination['last_page']);

        return $pagination['data'];
    }

    public function getListCompaniesByNotArray(){

        $company_arr = $this->company_group->getCompanyGroupArrayId();
        return Company::whereNotIn('id',$company_arr)->orderBy('name')->get()->toArray();
    }

    //get for application
    public function getCompaniesForApp(){
        return Company::select('fullname','id','address','tax_code')->get()->toArray();
    }

    public function uploadFileCompany($data){

        if($data['company_id'] != 0 || $data['company_id'] != null){

            $company = Company::find($data['company_id']);

            if($company){

                $tmp_layout = $company->layout_cards;

                $imgs  = json_decode($data['data'], true);

                if(count($imgs) > 0){

                    $obj_layout_card = new \stdClass;
                    $obj_layout_card->after = [];
                    $obj_layout_card->before = [];

                    foreach ($imgs as $k_imgs => $v_imgs) {
                        $v_imgs = (object)$v_imgs;
                        if($v_imgs->img){

                            if($v_imgs->opt_print == 'before') {
                                $obj_layout_card->before[] = $this->saveImgBase64($v_imgs->img, 'before_'.$data['company_id'].'_'.$k_imgs);
                            }

                            if($v_imgs->opt_print == 'after') {
                                $obj_layout_card->after[] = $this->saveImgBase64($v_imgs->img, 'after_'.$data['company_id'].'_'.$k_imgs);
                            }
                        }
                    }

                    $company->layout_cards = json_encode($obj_layout_card);

                    if($company->save()){

                        if($tmp_layout != null){

                            $tmp_layout = json_decode($tmp_layout, true);

                            $tmp_layout = (object)$tmp_layout;

                            foreach($tmp_layout->before as $v_before){
                                $this->removeImageBase64($v_before);
                            }
                            foreach($tmp_layout->after as $v_after){
                                $this->removeImageBase64($v_after);
                            }
                        }

                        return $company;
                    }
                }

            }

            return response('Company not found', 404);
        }

        return response('Upload file faild', 404);
    }

    public function getListCompanyByInputAndByTypeSearch($data)
    {
        $key_input = $data['key_input'];
        $style_search = $data['style_search'];

        $companies = Company::orderBy('name');

        if ($style_search == 'name') {
            $companies->where('name', 'like', "%$key_input%");
        }
        if ($style_search == 'tax_code') {
            $companies->where('tax_code', 'like', "%$key_input%");
        }
        if ($style_search == 'phone') {
            $companies->where('phone', 'like', "%$key_input%");
        }
        if ($style_search == 'address') {
            $companies->where('address', 'like', "%$key_input%");
        }

        return $companies->get()->toArray();
    }
}
