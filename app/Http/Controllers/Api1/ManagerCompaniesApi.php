<?php
namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Services\PushLogsService;
use App\Services\CompaniesService;
use Intervention\Image\ImageManagerStatic as Image;

class ManagerCompaniesApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;

     /**
     * @var App\Services\CompaniesService
     */
    protected $companies;

    /**
     * Constructor
     */
    public function __construct(Request $request, PushLogsService $push_logs, CompaniesService $companies)
    {
        $this->request = $request;
        $this->push_logs = $push_logs;
        $this->companies = $companies;
    }

    /**
     * Operation managerGetCompany
     *
     * current company of user logined.
     *
     *
     * @return Http response
     */
    public function managerGetCompany()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $company_id = $user->company_id;

        // get company
        $company = Company::find($company_id);

        if (empty($company))
            return response('Company Not found', 404);

        return $company;
    }

    /**
     * Operation managerUpdateCompany
     *
     * update.
     *
     *
     * @return Http response
     */
    public function managerUpdateCompany()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $company_id = $user->company_id;

        //path params validation
        $this->validate($this->request, [
            'address' => 'required',
            'tax_code' => 'required',
            'phone' => 'required',
            'email' => 'nullable|email|max:255'
        ]);

        // save Company
        $input = $this->request->all();

        $company = Company::find($company_id);

        if (empty($company))
            return response('Company Not found', 404);

        $company->address = $input['address'];
        $company->tax_code = $input['tax_code'];
        $company->phone = $input['phone'];
        $company->email = $input['email'];
        if($input['logo']){
          //remove log if this has
          if($company->logo) $this->removeImageBase64($company->logo);
          //save logo new
          $company->logo = $this->saveImgBase64($input['logo'], $company->id);
        }

        if ($company->save()) {

            // create Push Log for company
            $push_log = [];
            $push_log['action'] = 'update';
            $push_log['company_id'] = $company->id;
            $push_log['subject_id'] = $company->id;
            $push_log['subject_type'] = 'company';
            $push_log['subject_data'] = $company;
            $this->push_logs->createPushLog($push_log);
            return Company::find($company->id);
        }

        return response('Update Error', 404);
    }

    /**
     * Operation managerGetCompanyByNotArray
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerGetCompanyByNotArray()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return $this->companies->getListCompaniesByNotArray();
    }

    //func general -------------------------------------
    public function saveImgBase64($data, $middle){

        if($data){
            $image = explode(';', $data);

            //get file name
            $img_name = explode('/',  $image[0] );
            $file_name = $middle.'_'.time().'.'.$img_name[1];

            if(file_exists(public_path()."/img/logo-companies/")){

              $path = public_path()."/img/logo-companies/".$file_name;

              $img = Image::make(file_get_contents($data));
              $img->resize(null, 225);
              if( $img->save($path)){
                  return $file_name;
              }
            }
        }
    }
    public function removeImageBase64($file_name){

        if($file_name){
            $path = public_path()."/img/logo-companies/".$file_name;
            if(file_exists($path)){
                if(unlink($path)) return true;
            }
        }
    }
}
