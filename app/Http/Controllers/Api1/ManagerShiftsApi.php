<?php
namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use App\Models\Shift;
use Illuminate\Http\Request;
use App\Services\ShiftsService;

class ManagerShiftsApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\MembershipsService
     */
    protected $shifts;

    /**
     * Constructor
     */
    public function __construct(Request $request, ShiftsService $shifts)
    {
        $this->request = $request;
        $this->shifts = $shifts;
    }

    /**
     * Operation managerShiftsUpdateCollected
     *
     * shift.
     *
     *
     * @return Http response
     */
    public function managerShiftsUpdateCollected()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        if (count($input['shifts']) <= 0) {
            return response('The given data was invalid.', 404);            
        }

        $input['user'] = $user;

        return $this->shifts->updateCollected($input); 
    }

}
