<?php

/**
 * SMARTBUS API
 * No description provided (generated by Swagger Codegen https://github.com/swagger-api/swagger-codegen)
 *
 * OpenAPI spec version: 1.0.0
 * 
 *
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen.git
 * Do not edit the class manually.
 */


namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;

use App\Services\RfidCardsTestServices;

class AdminCardsApi extends ApiController
{
     /**
     * @var Illuminate\Http\Request
     */
    protected $request;

     /**
     * @var App\Services\RfidCardsTestServices
     */
    protected $rfid_cards;

    /**
     * Constructor
     */
    public function __construct(Request $request, RfidCardsTestServices $rfid_cards)
    {
        $this->request = $request;
        $this->rfid_cards = $rfid_cards;
    }

    /**
     * Operation listRfidCards
     *
     * list.
     *
     *
     * @return Http response
     */
    public function listRfidCards()
    {
       // check login
       $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);

       // save Company
       $input = $this->request->all();
        
       return $this->rfid_cards->getListRfidCards($input);
    }

     /**
     * Operation listRfidCardsByInputRfid
     *
     * list by input rfid.
     *
     * @param string $key_word  (required)
     *
     * @return Http response
     */
    public function listRfidCardsByInputRfid($key_word)
    {
      // check login
      $user = $this->requiredAuthUser();
      if (empty($user)) return response('token_invalid', 401);

      $input = $this->request->all();
      $input['input_rfid'] = $key_word;
      
      return $this->rfid_cards->getListRfidCardsByInputRfid($input);
    }

    /**
     * Operation createAndPrintRfidCard
     *
     * create.
     *
     *
     * @return Http response
     */
    public function createAndPrintRfidCard()
    {
       // check login
      $user = $this->requiredAuthUser();
      if (empty($user)) return response('token_invalid', 401);

      $input = $this->request->all();

      return $this->rfid_cards->createAndPrintRfidCard($input);      
    }
}
