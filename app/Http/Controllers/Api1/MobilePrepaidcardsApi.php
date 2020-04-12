<?php
namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\PrepaidCardsService;

class MobilePrepaidcardsApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * Constructor
     */
    public function __construct(Request $request, PrepaidCardsService $prepaidcards)
    {
        $this->request = $request; 
        $this->prepaidcards = $prepaidcards;      
    }

    /**
     * Operation mobileGetPrepaidcardByBarcode
     *
     * scan barcode.
     *
     * @param string $barcode  (required)
     *
     * @return Http response
     */
    public function mobileGetPrepaidcardByBarcode($barcode)
    {
        if (empty($barcode)) {
            return response('Invalid Barcode supplied', 404);
        }

        return $this->prepaidcards->getPrepaidCardByBarcode($barcode);
    }
}
