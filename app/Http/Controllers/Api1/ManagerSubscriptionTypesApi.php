<?php
namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Models\SubscriptionType;

class ManagerSubscriptionTypesApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * Constructor
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Operation managerUpdateSubscriptionType
     *
     * update.
     *
     *
     * @return Http response
     */
    public function managerUpdateSubscriptionType()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $company_id = $user->company_id;

        //path params validation
        $this->validate($this->request, [
            'id' => 'bail|required|integer|min:1',            
            'name' => 'required',
            'display_name' => 'required',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0'
        ]);

        // save
        $input = $this->request->all();

        // get Subscription Type
        $subscription_type = SubscriptionType::where('company_id', $company_id)
                                ->where('id', $input['id'])->first();

        if (empty($subscription_type)) 
            return response('Subscription Type Not found', 404);

        $subscription_type->name = $input['name'];
        $subscription_type->display_name = $input['display_name'];
        $subscription_type->duration = $input['duration'];
        $subscription_type->price = $input['price'];

        if ($subscription_type->save()) 
            return SubscriptionType::find($subscription_type->id);

        return response('Update Error', 404);                
    }

    /**
     * Operation managerlistSubscriptionTypes
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerlistSubscriptionTypes()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return SubscriptionType::where('company_id', $user->company_id)
                ->get()->toArray();
    }

    /**
     * Operation manmagerCreateSubscriptionType
     *
     * create.
     *
     *
     * @return Http response
     */
    public function manmagerCreateSubscriptionType()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $company_id = $user->company_id;

        //path params validation
        $this->validate($this->request, [
            'name' => 'required',
            'display_name' => 'required',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0'
        ]);

        // save
        $input = $this->request->all();

        $subscription_type = new SubscriptionType();
        $subscription_type->company_id = $company_id;
        $subscription_type->name = $input['name'];
        $subscription_type->display_name = $input['display_name'];
        $subscription_type->duration = $input['duration'];
        $subscription_type->price = $input['price'];

        if ($subscription_type->save()) 
            return SubscriptionType::find($subscription_type['id']);
  
        return response('Create Error', 404);         
    }

    /**
     * Operation managerDeleteSubscriptionType
     *
     * Delete.
     *
     * @param int $subscription_type_id  (required)
     *
     * @return Http response
     */
    public function managerDeleteSubscriptionType($subscription_type_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $company_id = $user->company_id;

        // check subscription type id
        if (empty($subscription_type_id) || (int)$subscription_type_id < 0) 
            return response('Invalid ID supplied', 404); 
        
        // get Subscription Type
        $subscription_type = SubscriptionType::where('company_id', $company_id)
                                ->where('id', $subscription_type_id)->first();

        if (empty($subscription_type)) 
            return response('Subscription Type Not found', 404);

        if ($subscription_type->delete())
            return response('OK', 200);
        
        return response('Delete Error', 404); 
    }

    /**
     * Operation managerGetSubscriptionTypeById
     *
     * Find by ID.
     *
     * @param int $subscription_type_id  (required)
     *
     * @return Http response
     */
    public function managerGetSubscriptionTypeById($subscription_type_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $company_id = $user->company_id;

        // check subscription type id
        if (empty($subscription_type_id) || (int)$subscription_type_id < 0) 
            return response('Invalid ID supplied', 404);
            
        // get Subscription Type
        $subscription_type = SubscriptionType::where('company_id', $company_id)
                                ->where('id', $subscription_type_id)->first();

        if (empty($subscription_type)) 
            return response('Subscription Type Not found', 404);

        return $subscription_type;                    
    }    
}