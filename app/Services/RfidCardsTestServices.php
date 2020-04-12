<?php
namespace App\Services;

use App\Models\RfidCard;
use App\Models\Membership;
use Carbon\Carbon;

use App\Services\UsersService;
use App\Services\VehiclesService;
use App\Services\MembershipsService;
use App\Services\CompaniesService;
use App\Services\SubscriptionsService;

class RfidCardsTestServices
{
    /**
     * @var App\Services\UsersService
     */
    protected $users;

    /**
     * @var App\Services\VehiclesService
     */
    protected $vehicles;

    /**
     * @var App\Services\MembershipsService
     */
    protected $members;

    /**
     * @var App\Services\PrepaidCardsService
     */
    protected $prepaidcards;

    /**
     * @var App\Services\CompaniesService
     */
    protected $companies;

    /**
     * @var App\Services\SubscriptionsService
     */
    protected $subscriptions;

    public function __construct( 
        UsersService  $users,
        VehiclesService $vehicles,
        MembershipsService $members,
        PrepaidCardsService $prepaidcards,
        CompaniesService $companies,
        SubscriptionsService $subscriptions
    ) 
    {
        $this->users = $users;
        $this->vehicles = $vehicles;
        $this->members = $members;
        $this->prepaidcards = $prepaidcards;
        $this->companies = $companies;
        $this->subscriptions = $subscriptions;
    }

    public function getListRfidCards($data){
    
        $limit = $data['limit'];

        if (empty($limit) && $limit < 0)
            $limit = 10;

        if (empty($data['company_id'])) {

            $pagination = RfidCard::where('rfidcards.deleted_at','=', NULL)
                            ->orderBy('created_at')
                            ->paginate($limit)
                            ->toArray();
            
            $rfidcards_arr = [];

            if (count($pagination['data']) > 0) {

                foreach ($pagination['data'] as $rfidcard) {

                    $company_name = '';
                    $created_at = $rfidcard['created_at'];
                    $name = '';
                    $phone = '';
                    $balance = NULL;
                
                    if($rfidcard['target_id'] > 0){

                        if($rfidcard['usage_type'] == 'user'){

                            $user_id =  $rfidcard['target_id'];
                            
                            $user = $this->users->getUsersById($user_id);

                            if(!empty($user)){

                                $name = $user->fullname;
                                $phone = $user->phone;
                                $balance = NULL;
                                $company_id = $user->company_id;
                                $company = $this->companies->getCompanyById( $company_id);

                                if(!empty($company)){ $company_name = $company->name;}
                            }
                        } 

                        if($rfidcard['usage_type'] == 'vehicle'){
                            
                            $vehicle_id =  $rfidcard['target_id'];
                          
                            $vehicle = $this->vehicles->getVehicleById($vehicle_id);

                            if(!empty($vehicle)){

                                $name = $vehicle->license_plates;
                                $phone = NULL;
                                $balance = NULL;
                                $company_id = $vehicle->company_id;
                                $company = $this->companies->getCompanyById( $company_id);

                                if(!empty($company)){ $company_name = $company->name;}
                            }else{

                            }

                        } 

                        if($rfidcard['usage_type'] == 'member'){

                            $member_id =  $rfidcard['target_id'];
                          
                            $member = $this->members->getByMembershipId($member_id);

                            if(!empty($member)){
                                
                                $name = $member->fullname;
                                $phone = $member->phone;
                                $balance = $member->balance;
                                $company_id = $member->company_id;
                                $company =  $this->companies->getCompanyById( $company_id);

                                if(!empty($company)){ $company_name = $company->name;}
                            }
                        }
                        
                        if($rfidcard['usage_type'] == 'prepaidcard'){

                            $prepaidcard_id =  $rfidcard['target_id'];
                          
                            $prepaidcard = $this->prepaidcards->getPrepaidcardById($prepaidcard_id);

                            if(!empty($prepaidcard)){
                                
                                $name = NULL;
                                $phone = NULL;
                                $balance = $prepaidcard->balance;
                                $company_id = $prepaidcard->company_id;
                                $company =  $this->companies->getCompanyById( $company_id);

                                if(!empty($company)){ $company_name = $company->name;}
                            }
                        }
                    }

                    $rfid_card_tmp = array(
                        'rfidcard' =>  $rfidcard['rfid'],
                        'barcode' =>  $rfidcard['barcode'],
                        'usage_type' =>  $rfidcard['usage_type'],
                        'company_name' =>  $company_name,
                        'name' =>   $name,
                        'phone' =>    $phone,
                        'balance' =>  $balance,
                        'created_at' => Carbon::parse($created_at)->format('d/m/Y H:i:s')
                    );
                    array_push($rfidcards_arr,$rfid_card_tmp);
                }
            }

            header("pagination-total: ".$pagination['total']);
            header("pagination-current: ".$pagination['current_page']);
            header("pagination-last: ".$pagination['last_page']);

            return $rfidcards_arr;
        }else {

            $company_id = $data['company_id'];
            $rfidcards_arr = [];

            $pagination = Membership::with('rfidcard', 'company')
                                ->where('memberships.company_id', '=', $company_id)
                                ->join('memberships_subscription', 'memberships_subscription.membership_id', '=', 'memberships.id')
                                ->where('memberships_subscription.deleted_at','=', NULL)
                                ->orderBy('memberships.created_at', 'desc')
                                ->paginate($limit)
                                ->toArray();
            if(count($pagination) > 0){
                
                foreach ($pagination['data'] as $membership) {

                    $rfid = null;
                    $barcode = null;
                    $us_type = '';
                    $name = null;
                    $phone = null;
                    $balance = 0; 
                    $created_at = null;
                    
                    $subscription_id =  $membership['subscription_id'];
                    $subscription = $this->subscriptions->getById($subscription_id,$membership['company_id']);
                    if(!empty(  $subscription)){
                        $us_type = $subscription->name;
                    }

                    $member_tmp = array(
                        'id' => $membership['id'],
                        'rfid' => $membership['rfidcard']['rfid'],
                        'barcode' => $membership['rfidcard']['barcode'],
                        'subscription_type_id' =>  $subscription_id,
                        'rfidcard_id'=>  $membership['rfidcard_id'],
                        'use_type' => $us_type,
                        'name' => $membership['fullname'],
                        'phone' => $membership['phone'],
                        'balance' => $membership['balance'],
                        'expiration_date' => Carbon::parse($membership['expiration_date'])->format('d/m/Y H:i:s') ,
                        'created_at' => Carbon::parse($membership['created_at'])->format('d/m/Y H:i:s') 
                    );
                    array_push( $rfidcards_arr ,$member_tmp);
                }
            }                  

            header("pagination-total: ".$pagination['total']);
            header("pagination-current: ".$pagination['current_page']);
            header("pagination-last: ".$pagination['last_page']);

            return $rfidcards_arr;
        }
    }

    public function getListRfidCardsByInputRfid($data){

        $rfidcards = RfidCard::where('rfid', 'like', '%'.$data['input_rfid'].'%')
                        ->orderBy('created_at')
                        ->get()
                        ->toArray();
        
        $rfidcards_arr = [];

        foreach ($rfidcards as $rfidcard) {

            $company_name = '';
            $created_at = $rfidcard['created_at'];
            $name = '';
            $phone = '';
            $balance = NULL;
        
            if($rfidcard['target_id'] > 0){

                if($rfidcard['usage_type'] == 'user'){

                    $user_id =  $rfidcard['target_id'];
                    
                    $user = $this->users->getUsersById($user_id);

                    if(!empty($user)){

                        $name = $user->fullname;
                        $phone = $user->phone;
                        $balance = NULL;
                        $company_id = $user->company_id;
                        $company = $this->companies->getCompanyById( $company_id);

                        if(!empty($company)){ $company_name = $company->name;}
                    }
                } 

                if($rfidcard['usage_type'] == 'vehicle'){
                    
                    $vehicle_id =  $rfidcard['target_id'];
                    
                    $vehicle = $this->vehicles->getVehicleById($vehicle_id);

                    if(!empty($vehicle)){

                        $name = $vehicle->license_plates;
                        $phone = NULL;
                        $balance = NULL;
                        $company_id = $vehicle->company_id;
                        $company = $this->companies->getCompanyById( $company_id);

                        if(!empty($company)){ $company_name = $company->name;}
                    }else{

                    }

                } 

                if($rfidcard['usage_type'] == 'member'){

                    $member_id =  $rfidcard['target_id'];
                    
                    $member = $this->members->getByMembershipId($member_id);

                    if(!empty($member)){
                        
                        $name = $member->fullname;
                        $phone = $member->phone;
                        $balance = $member->balance;
                        $company_id = $member->company_id;
                        $company =  $this->companies->getCompanyById( $company_id);

                        if(!empty($company)){ $company_name = $company->name;}
                    }
                }
                
                if($rfidcard['usage_type'] == 'prepaidcard'){

                    $prepaidcard_id =  $rfidcard['target_id'];
                    
                    $prepaidcard = $this->prepaidcards->getPrepaidcardById($prepaidcard_id);

                    if(!empty($prepaidcard)){
                        
                        $name = NULL;
                        $phone = NULL;
                        $balance = $prepaidcard->balance;
                        $company_id = $prepaidcard->company_id;
                        $company =  $this->companies->getCompanyById( $company_id);

                        if(!empty($company)){ $company_name = $company->name;}
                    }
                }
            }

            $rfid_card_tmp = array(
                'rfidcard' =>  $rfidcard['rfid'],
                'barcode' =>  $rfidcard['barcode'],
                'usage_type' =>  $rfidcard['usage_type'],
                'company_name' =>  $company_name,
                'name' =>   $name,
                'phone' =>    $phone,
                'balance' =>  $balance,
                'created_at' => Carbon::parse($created_at)->format('d/m/Y H:i:s')
            );
            array_push($rfidcards_arr,$rfid_card_tmp);
        }
        

        return $rfidcards_arr;
        
    }

    public function createAndPrintRfidCard($data){

        $rfid = $data['rfid'];

        if(RfidCard::where('rfid', $rfid)->exists()) {
            return response('The rfid card already exists.', 404);
        }

        $rfidcard = new RfidCard();
        $rfidcard->rfid = $rfid;

        $digits = 13;
        $barcode_flag = false;
        $barcode = '';
        while(!$barcode_flag) {
            $random = rand(0, pow(10, 10)-1);
            $barcode = strtoupper(str_pad($random, $digits, 'MBS', STR_PAD_LEFT));
            if (!RfidCard::where('barcode', $barcode)->exists()) { $barcode_flag = true; }
            $rfidcard->barcode = $barcode;
        }

        if ($rfidcard->save()) {
            return $rfidcard;
        }

        return response('Create Error', 404);
    }

}
