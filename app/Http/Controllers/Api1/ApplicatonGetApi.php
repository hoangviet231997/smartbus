<?php

namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\PublicFunctionService;
use App\Services\PartnersService;
use App\Services\CompaniesService;
use App\Services\RoutesService;

class ApplicatonGetApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

     /**
     * @var App\Services\RfidCardsService
     */
    protected $public_functions;  

    /**
     * @var App\Services\PartnersService
     */
    protected $partner_codes; 

    /**
     * @var App\Services\CompaniesService
     */
    protected $companies; 

    /**
     * @var App\Services\RoutesService
     */
    protected $routes; 

    /**
     * Constructor
     */
    public function __construct(
        Request $request, 
        PublicFunctionService $public_functions,
        PartnersService $partner_codes,
        CompaniesService $companies,
        RoutesService $routes
    )
    {
        $this->request = $request;
        $this->public_functions = $public_functions;
        $this->partner_codes = $partner_codes;
        $this->companies = $companies;
        $this->routes = $routes;
    }

    /**
     * Operation getListCompanies
     *
     * get list companies.
     *
     *
     * @return Http response
     */
    public function getListCompanies()
    {
        $token = $this->request->header('Token');
        $partner_code = $this->request->header('partnerCode');

        if(empty($token)) {  return ['status'=> 404, 'message'=> 'Mã token không tìm thấy', 'data' => []]; }
        if(empty($partner_code)) {  return ['status'=> 404, 'message'=> 'Mã đối tác không tìm thấy', 'data' => []]; }

        $partner = $this->partner_codes->getPartnerByPartnerCode($partner_code);

        if(empty($partner)){ return ['status'=> 404, 'message'=> 'Mã đối tác không tồn tại', 'data' => []];  }
        
        $app_key = $partner->app_key;

        $token_de = json_decode($this->public_functions->deCrypto($token, $partner_code));

        if(empty($token_de)){ return ['status'=> 404, 'message'=> 'Giải mã token thất bại', 'data' => []]; }

        $app_key_de = $token_de->appKey;

        if(empty($app_key_de)){ return ['status'=> 404, 'message'=> 'Giải mã token thất bại', 'data' => []]; }
        if($app_key_de != $app_key){ return ['status'=> 404, 'message'=> 'Giải mã token thất bại', 'data' => []]; }
       
        try {
            return $this->companies->getCompaniesForApp();
        } catch (Exception $e) {
            return response('Data not found', 404);
        }
    }
    /**
     * Operation getListRoutes
     *
     * get list routes.
     *
     * @return Http response
     */
    public function getListRoutes()
    {
        $token = $this->request->header('Token');
        $partner_code = $this->request->header('partnerCode');
        $company_id = $this->request->header('companyId');

        if(empty($token)) {  return ['status'=> 404, 'message'=> 'Mã token không tìm thấy', 'data' => []]; }
        if(empty($partner_code)) {  return ['status'=> 404, 'message'=> 'Mã đối tác không tìm thấy', 'data' => []]; }
        if(empty($company_id)) {  return ['status'=> 404, 'message'=> 'Mã công ty không tìm thấy', 'data' => []]; }

        $partner = $this->partner_codes->getPartnerByPartnerCode($partner_code);

        if(empty($partner)){ return ['status'=> 404, 'message'=> 'Mã đối tác không tồn tại', 'data' => []];  }
        
        $app_key = $partner->app_key;

        $token_de = json_decode($this->public_functions->deCrypto($token, $partner_code));

        if(empty($token_de)){ return ['status'=> 404, 'message'=> 'Giải mã token thất bại', 'data' => []]; }

        $app_key_de = $token_de->appKey;

        if(empty($app_key_de)){ return ['status'=> 404, 'message'=> 'Giải mã token thất bại', 'data' => []]; }
        if($app_key_de != $app_key){ return ['status'=> 404, 'message'=> 'Giải mã token thất bại', 'data' => []]; }

        try {
            return $this->routes->getRoutesByCompaniesForApp($company_id);
        } catch (Exception $e) {
            return response('Data not found', 404);
        }
    }
}
