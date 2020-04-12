<?php
namespace App\Services;

use App\Models\MembershipSubscription;
use App\Services\SubscriptionsService;

class MembershipsSubscriptionService
{
    /**
     * @var App\Services\SubscriptionsService
     */
    protected $subscriptions;
    
    public function __construct(SubscriptionsService $subscriptions)
    {
        $this->subscriptions = $subscriptions;
    }

    public function create($membership_id, $subscription_id, $company_id)
    {
        // get subscription
        $subscription = $this->subscriptions->getById($subscription_id, $company_id);
        $duration = $subscription->duration;
        $date = date("Y-m-d h:i:s");

        $member_sub = new MembershipSubscription();
        $member_sub->membership_id = $membership_id;
        $member_sub->subscription_id = $subscription_id;
        $member_sub->expiration_date = date("Y-m-d h:i:s", strtotime( $date.' +'.$duration.' day' ));
        $member_sub->save();

        if ($member_sub->save())
            return $this->getByMembershipId($member_sub['id']);

        return response('Create Error', 404);
    }

    public function getByMembershipId($id)
    {
        return MembershipSubscription::where('membership_id', $id)->first();
    }

    public function getBySubscriptionId($id)
    {
        return MembershipSubscription::where('subscription_id', $id)->first();
    } 
    
    public function getDataByMembershipIdAndSubscriptionId($membership_id, $subscription_id){
        return MembershipSubscription::where('membership_id',$membership_id)
                                    ->where('subscription_id',$subscription_id)
                                    ->first();
    }
}    