<?php
namespace App\Services;
use App\Models\SubscriptionType;

class SubscriptionsService
{
    public function checkExist($id, $company_id)
    {
        return SubscriptionType::where('id', $id)
                    ->where('company_id', $company_id)
                    ->exists();
    }

    public function getById($id, $company_id)
    {
        return SubscriptionType::where('id', $id)
                    ->where('company_id', $company_id)
                    ->first();
    }

    public function getList()
    {
        return SubscriptionType::all()->get();
    }
}