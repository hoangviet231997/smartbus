<?php
namespace App\Services;

use App\Models\ShiftDestroy;
use App\Services\ShiftsService;
use App\Services\TransactionsService;
use App\Services\UsersService;

use Illuminate\Support\Facades\DB;

class ShiftDestroysService
{
    /**
     * @var App\Services\ShiftsService
     */
    protected $shifts;

    /**
     * @var App\Services\TransactionsService
     */
    protected $transactions;

    /**
     * @var App\Services\UsersService
     */
    protected $users;

    public function __construct(
        ShiftsService $shifts,
        TransactionsService $transactions,
        UsersService $users

    )
    {
        $this->shifts = $shifts;
        $this->transactions = $transactions;
        $this->users = $users;

    }

    public function addShiftDestroy($data){

        $company_id = $data['company_id'];
        $user_id = $data['user_id'];
        $shift_id = $data['shift_id'];
        $route_id = $data['route_id'];      
        $license_plates = $data['license_plates'];    
        $description = $data['description'];
        $total_charge = $data['total_charge'];
        $total_deposit = $data['total_deposit'];
        $total_pos = $data['total_pos'];
        $work_time = $data['work_time'];

        if ($data) {
            
            $shift = $this->shifts->getShiftById($shift_id);

            if (empty($shift)) {
                return response('Shift not found', 404);
            }

            $shift_destroy = new ShiftDestroy();

            $shift_destroy->company_id = $company_id;
            $shift_destroy->user_id = $user_id;
            $shift_destroy->shift_id = $shift_id;          
            $shift_destroy->route_id = $route_id;
            $shift_destroy->description = $description;
            $shift_destroy->accept = 0;
            $shift_destroy->total_pos = $total_pos;
            $shift_destroy->total_charge = $total_charge;
            $shift_destroy->total_deposit = $total_deposit;
            $shift_destroy->subdriver_id = $shift->subdriver_id;
            $shift_destroy->license_plates = $license_plates;
            $shift_destroy->driver_id = $shift->user_id;
            $shift_destroy->work_time = $work_time;
            
            if ($shift_destroy->save()) {

                // update ticket_destroys = -1 by shift_id
                DB::table('transactions')->where('shift_id',$shift_id)->update(['ticket_destroy' => -1]);

                // wait confirm shift destory :   $shift->shift_destroy = -1
                // normal  shift destory :  $shift->shift_destroy =  0
                // confirmed shift destory :   $shift->shift_destroy =  1
                $shift->shift_destroy = -1;
                $shift->save();

                return ['status' => true, 'message' => 'OK'];
            }
        }
        return response('Send repuest not found', 404);
    }

    public function viewShiftDestroy($data){

        if ($data) {
            
            $company_id = $data['company_id'];
            $accept = $data['accept'] ?? null;
            $from_date = date("Y-m-d 00:00:00", strtotime($data['from_date']));
            $to_date = date("Y-m-d 23:59:59", strtotime($data['to_date']));

            $shift_destroys = [];
            $where_accept = [];

            switch ($accept) {
                case null:
                    $where_accept = [0];
                    break;
                case 0:
                    $where_accept = [1,-1];
                    break;
                case 1:
                    $where_accept = [1];
                    break;
                case -1:
                    $where_accept = [-1];
                    break;
            }

            $shift_destroys = ShiftDestroy::where('company_id',$company_id)
                ->whereIn('accept', $where_accept)
                ->with('user', 'route', 'driver', 'subdriver')
                ->where('created_at', '>=', $from_date)
                ->where('created_at', '<=', $to_date)
                ->orderBy('created_at','desc')
                ->get();
            
            return $shift_destroys;
        }
    }

    public function acceptShiftDestroy($data){

        $shift_destroy_id = $data['id'];
        $type = $data['type'];

        $shift_destroy = $this->getShiftDestroyById($shift_destroy_id);
        $shift = $this->shifts->getShiftById($shift_destroy->shift_id);

        if ($shift_destroy) {
            
            // accept shift destory :   $shift_destroy->accept = -1
            // normal  shift destory :  $shift_destroy->accept =  0
            // refuse shift destory :   $shift_destroy->accept =  1

            if ($type == 'accept') {
                $shift_destroy->accept = -1;

                if ($shift_destroy->save()) {
                    // update ticket_destroys = 1 by shift_id
                    DB::table('transactions')->where('shift_id',$shift_destroy->shift_id)->update(['ticket_destroy' => 1]);

                    $shift->shift_destroy = 1;
                    $shift->save();

                    return ['msg' => 'Thành công! ', 'status' => 200];  
                }
            }

            if ($type == 'refuse') {
                $shift_destroy->accept = 1;
                
                if ($shift_destroy->save()) {
                    // update ticket_destroys = 0 by shift_id
                    DB::table('transactions')->where('shift_id',$shift_destroy->shift_id)->update(['ticket_destroy' => 0]);

                    $shift->shift_destroy = 0;
                    $shift->save();

                    return ['msg' => 'Thành công! ', 'status' => 200];  
                }
            }
        }
        
        return response('Data not found', 404);
    }

    public function getShiftDestroyById($shift_destroy_id){

        return ShiftDestroy::where('id',$shift_destroy_id)->first();
    }
}