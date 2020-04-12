<?php
namespace App\Services;

use App\Models\PrepaidCard;
use App\Models\Transaction;
use App\Models\RfidCard;
use App\Services\RfidCardsService;
use App\Services\PushLogsService;

class PrepaidCardsService
{
    /**
     * @var App\Services\RfidCardsService
     */
    protected $rfidcards;

    /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;

    public function __construct(PushLogsService $push_logs, RfidCardsService $rfidcards)
    {
        $this->rfidcards = $rfidcards;
        $this->push_logs = $push_logs;
    }

    public function createPrepaidCard($data)
    {
        $rfid = $data['rfid'];
        $barcode = $data['barcode'];
        $company_id = $data['company_id'];
        $balance = (double) $data['balance'];

        if (!$this->rfidcards->checkRfidCardExist($rfid, $barcode)) {
            return response('Rfid card Not found', 404);
        }

        if ($this->rfidcards->checkRfidCardUsed($rfid, $barcode)) {
            return response('The rfid card has been used.', 404);
        }

        // get and update rfid card
        $rfidcard = $this->rfidcards->getRfidCardByRfid($rfid, $barcode);

        $prepaidcard = new PrepaidCard();
        $prepaidcard->company_id = $company_id;
        $prepaidcard->rfidcard_id = $rfidcard->id;
        $prepaidcard->balance = $balance;

        if ($prepaidcard->save()) {

            $prepaidcard['rfid'] = $rfidcard->rfid;
            $prepaidcard['barcode'] = $barcode;

            // create Push Log
            $push_log = [];
            $push_log['action'] = 'update';
            $push_log['company_id'] = $company_id;
            $push_log['subject_id'] = $prepaidcard['id'];
            $push_log['subject_type'] = 'rfidcard';
            $push_log['subject_data'] = $prepaidcard;
            $this->push_logs->createPushLog($push_log);

            if (!empty($rfidcard) ) {
                $this->rfidcards->updateTargetAndUsage(
                    $rfidcard->id,
                    $company_id,
                    $prepaidcard['id'],
                    'prepaidcard'
                );
            }
            return $this->getPrepaidcardByIdAndCompanyId($prepaidcard['id'], $company_id);
        }
        return response('Create Error', 404);
    }

    public function updatePrepaidCard($data)
    {
        $rfid = $data['rfid'];
        $barcode = $data['barcode'];
        $company_id = $data['company_id'];
        $balance = (double) $data['balance'];

        if (!$this->rfidcards->checkRfidCardExist($rfid, $barcode)) {
            return response('Rfid card Not found', 404);
        }

        // get rfid card
        $rfidcard = $this->rfidcards->searchRfidCardByOptions([
                        ['rfid', $rfid],
                        ['barcode', $barcode],
                        ['usage_type', 'prepaidcard']
                    ]);

        if (empty($rfidcard)) {
            return response('Rfid card Not found', 404);
        }

        // get prepaidcard
        $prepaid_id = $rfidcard->target_id;
        $prepaidcard = $this->getPrepaidcardByIdAndCompanyId($prepaid_id, $company_id);

        if (empty($rfidcard)) {
            return response('Prepaid card Not found', 404);
        }

        $prepaidcard->balance = (double) $prepaidcard->balance + $balance;

         if ($prepaidcard->save()) {

            $prepaidcard->rfid = $rfidcard->rfid;
            $prepaidcard->barcode = $barcode;
            // create Push Log
            $push_log = [];
            $push_log['action'] = 'update';
            $push_log['company_id'] = $company_id;
            $push_log['subject_id'] = $prepaidcard->id;
            $push_log['subject_type'] = 'rfidcard';
            $push_log['subject_data'] = $prepaidcard;
            $this->push_logs->createPushLog($push_log);

            return $this->getPrepaidcardByIdAndCompanyId($prepaidcard->id, $company_id);
        }
        return response('Update Error', 404);
    }

    public function getPrepaidcardByIdAndCompanyId($id, $company_id)
    {
        return PrepaidCard::where('id', $id)
                    ->where('company_id', $company_id)
                    ->first();
    }

    public function getPrepaidcardById($id)
    {
        return PrepaidCard::where('id', $id)
                    ->first();
    }

    public function getPrepaidcardByIdAndbyCompanyId($id)
    {
        return PrepaidCard::where('id', $id)
                    ->where('company_id', $company_id)
                    ->first();
    }

    public function depositPrepaidCard($data)
    {
        $rfid = $data['rfid'];
        $amount = $data['amount'];
        $company_id = $data['company_id'];
        $user_id = $data['user_id'];
        $type = $data['type'];

        // check rfid card
        $rfidcard = $this->rfidcards->searchRfidCardByOptions([
                        ['rfid', $rfid]
                    ]);

        if (empty($rfidcard)) {
            return response('Rfid card Not found', 404);
        }

        $usage_type = $rfidcard->usage_type;
        $target_id = $rfidcard->target_id;

        $prepaidcard = [];
        $prepaidcard['rfid'] = $rfid;
        $prepaidcard['barcode'] = $rfidcard->barcode;
        $prepaidcard['company_id'] = $company_id;
        $prepaidcard['balance'] = $amount;

        if (!empty($usage_type) && !empty($target_id)) {

            if ($usage_type != 'prepaidcard') {
                return response('Card Error', 404);
            }

            //update balance
            return $this->updatePrepaidCard($prepaidcard);
        } else {

            // create prepaid card
            return $this->createPrepaidCard($prepaidcard);
        }
    }

    public function chargePrepaidCard($data)
    {
        $rfid = $data['rfid'];
        $amount = (double) $data['amount'];
        $company_id = $data['company_id'];
        $user_id = $data['user_id'];
        $type = $data['type'];

        // get rfid card
        $rfidcard = $this->rfidcards->searchRfidCardByOptions([
                        ['rfid', $rfid],
                        ['usage_type', 'prepaidcard']
                    ]);

        if (empty($rfidcard)) {
            return response('Rfid card not found', 404);
        }

        $rfidcard_id = $rfidcard->id;
        $target_id = $rfidcard->target_id;
        $barcode = $rfidcard->barcode;
        // get prepaid card
        $prepaidcard = $this->getPrepaidCardByOptions([
                            ['id', $target_id],
                            ['company_id', $company_id],
                            ['rfidcard_id', $rfidcard_id]
                        ]);

        if (empty($prepaidcard)) {
            return response('Prepaid card not found', 404);
        }

        $balance = (double) $prepaidcard->balance;

        if ($balance < $amount) {
            return response('Not enough balance', 404);
        }

        $prepaidcard->balance = $balance - $amount;

        if ($prepaidcard->save()) {

            $prepaidcard->rfid = $rfidcard->rfid;
            $prepaidcard->barcode = $barcode;
            // create Push Log
            $push_log = [];
            $push_log['action'] = 'update';
            $push_log['company_id'] = $company_id;
            $push_log['subject_id'] = $prepaidcard->id;
            $push_log['subject_type'] = 'rfidcard';
            $push_log['subject_data'] = $prepaidcard;
            $this->push_logs->createPushLog($push_log);

            return $prepaidcard;
        }

        return response('Charge error', 404);
    }

    public function getPrepaidCardByOptions($options = [])
    {

        if (count($options) > 0) {
            foreach ($options as $key => $option) {
                if (count($option) == 2 && empty($option[1]))
                    unset($options[$key]);
            }

           return PrepaidCard::where($options)->first();
        }

        return response('Rfid card Not found', 404);
    }

    public function getPrepaidCardByBarcode($barcode)
    {

        // get rfid card
        $rfidcard = $this->rfidcards->searchRfidCardByOptions([
                        ['barcode', $barcode],
                        ['usage_type', 'prepaidcard']
                    ]);

        if (empty($rfidcard)) {
            return response('Rfid card not found', 404);
        }

        // get prepaid card
        $rfidcard_id = $rfidcard->id;
        $prepaidcard = $this->getPrepaidCardByOptions([
                            ['rfidcard_id', $rfidcard_id]
                        ]);

        if (empty($prepaidcard)) {
            return response('Prepaid card not found', 404);
        }

        return $prepaidcard;
    }
}
