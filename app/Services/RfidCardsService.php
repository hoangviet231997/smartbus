<?php
namespace App\Services;

use App\Models\RfidCard;
use App\Services\PushLogsService;
use DB;


class RfidCardsService
{
    /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;

    public function __construct(PushLogsService $push_logs)
    {
        $this->push_logs = $push_logs;
    }

    public function insertRfidCard($data){

        $rfidcard = new RfidCard();
        $rfidcard->rfid = $data['rfid'];
        $rfidcard->created_at = $data['created'];
        $rfidcard->updated_at = $data['created'];

        $rfidcard->usage_type = "membership";

        if ($rfidcard->save()) {
            return $rfidcard;
        }
    }

    public function editRfidCard($data){

        $rfidcard = RfidCard::find($data['rfidcard']);
        $rfidcard->target_id = $data['membership_id'];
        $rfidcard->usage_type = "membership";

        if ($rfidcard->save()) {
            return $rfidcard;
        }
    }

    public function rfidCardById($id){
        return RfidCard::where('id', $id)->first();
    }

    public function rfidCardByRfid($rfid) {
        return RfidCard::where('rfid', '=', $rfid)
                        ->join('memberships', 'memberships.rfidcard_id', '=', 'rfidcards.id')
                        ->select('memberships.*')
                        ->first();
    }

    public function rfidCardByRfidNotJoin($rfid) {
        return RfidCard::where('rfid', '=', $rfid)
                        ->first();
    }

    public function checkRfidCardUsed($rfid){
        return RfidCard::where('rfid', $rfid)
                    ->where('usage_type', '!=', null)
                    ->where('target_id', '!=', null)
                    ->exists();
    }

    //======================================

    public function checkRfidCardExist($rfid){
        return RfidCard::where('rfid', $rfid)->exists();
    }

    public function createRfidCard($data){
        $rfid = $data['rfid'];
        $barcode = $data['barcode'];

        if(RfidCard::where('rfid', $rfid)->exists() ||
            RfidCard::where('barcode', $barcode)->exists()) {
            return response('The rfid card already exists.', 404);
        }

        $rfidcard = new RfidCard();
        $rfidcard->rfid = $rfid;
        $rfidcard->barcode = $barcode;

        if ($rfidcard->save()) {

            return $this->getRfidCardById($rfidcard['id']);
        }

        return response('Create Error', 404);
    }

    public function updateRfidCard($data) {
        $id = $data['id'];
        $rfid = $data['rfid'];
        $barcode = $data['barcode'];

        $rfidcard = $this->getRfidCardById($id);

        if (empty($rfidcard))
            return response('Rfid card Not found', 404);

        if ($rfidcard->rfid != $rfid) {
            if (RfidCard::where('rfid', $rfid)->exists())
                return response('The rfid card already exists.', 404);
        }

        if ($rfidcard->barcode != $barcode) {
            if (RfidCard::where('barcode', $barcode)->exists())
                return response('The rfid card already exists.', 404);
        }

        $rfidcard->rfid = $rfid;
        $rfidcard->barcode = $barcode;

        if ($rfidcard->save()) {

            return $this->getRfidCardById($rfidcard['id']);
        }

        return response('Update Error', 404);
    }

    public function updateTargetAndUsage($id, $company_id, $target_id, $usage_type){
        $rfidcard = $this->getRfidCardById($id);
        $rfidcard->target_id = $target_id;
        $rfidcard->usage_type = $usage_type;
        $rfidcard->save();
    }

    public function deleteRfidCard($id){

        $rfidcard = $this->getRfidCardById($id);

        if (empty($rfidcard))
            return response('Rfid card Not found', 404);

        if ($rfidcard->delete())
            return response('OK', 200);

        return response('Delete Error', 404);
    }

    public function getDataByRfidAnBarcode($rfid, $barcode){
        return RfidCard::where('rfid', $rfid)->where('barcode',$barcode)->first();
    }

    public function getExistsByBarcodeByNotId($barcode, $id){

        return RfidCard::where('barcode', $barcode)
                        ->where('id', '!=', $id)
                        ->exists();
    }

    public function getExistsByBarcode($barcode){

        return RfidCard::where('barcode', $barcode)->exists();
    }

    public function getDataByBarcode($barcode){

        return RfidCard::where('barcode', $barcode)->first();
    }

    public function getRfidCardByRfid($rfid){
        return RfidCard::where('rfid', $rfid)->first();
    }

    public function getRfidCardByRfidAndTargetIdAndUsageType($rfid, $target_id, $usage_type){
        return RfidCard::where('rfid', $rfid)->where('target_id',$target_id)->where('usage_type', $usage_type)->first();
    }

    public function getRfidCardByLikeBarcode($key_word, $property_result){
        return RfidCard::where('barcode', 'like' , '%'.$key_word.'%')->pluck($property_result)->toArray();
    }

    public function getRfidCardByLikeRfid($key_word, $property_result){
        return RfidCard::where('rfid', 'like' , '%'.$key_word.'%')->pluck($property_result)->toArray();
    }

    public function getRfidCardById($id){
        return RfidCard::where('id', $id)->first();
    }

    public function searchRfidCardByOptions($options = []) {

        if (count($options) > 0) {

            foreach ($options as $key => $option) {

                if (count($option) == 2 && empty($option[1]))
                    unset($options[$key]);
            }

           return RfidCard::where($options)->first();
        }

        return response('Search Error', 404);
    }

    public function getRfidCardNotInArrayByKeyByUsageTypeByComnpanyId($key,$usage_type,$company_id,$arr){

        return RfidCard::where('rfidcards.usage_type', $usage_type)
                        ->whereNotIn('rfidcards.'.$key, $arr)
                        ->join('memberships', 'memberships.rfidcard_id', '=', 'rfidcards.id')
                        ->where('memberships.company_id', $company_id)
                        ->where('memberships.balance','>',0)
                        ->select(
                            'memberships.fullname',
                            'memberships.phone',
                            'memberships.balance',
                            'memberships.membershiptype_id',
                            'rfidcards.rfid',
                            'rfidcards.barcode'
                        )
                        ->get()
                        ->toArray();

    }
}
