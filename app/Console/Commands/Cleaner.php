<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PushLogsService;

class Cleaner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:cleaner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(PushLogsService $push_logs_service)
    {
        // clean push logs is voucher
        $push_logs = $push_logs_service->getPushLogByOptions([
                        ['subject_type', 'voucher']
                    ]);

        if (count($push_logs) > 0) {
            
            foreach ($push_logs as $push_log) {
               $subject_data = json_decode($push_log->subject_data);

                if ($subject_data) {

                    if (!empty($subject_data->startdate) && 
                        !empty($subject_data->duration)) {

                        $start_date = $subject_data->startdate + $subject_data->duration;
                        $current_date = time();

                        if ($start_date < $current_date) {
                            $push_logs_service->deletePushLog($push_log->id);
                            $this->info('Delete id:'.$push_log->id.' success');
                        }
                    }
                }
            }
        }

        //-------------------------##########-------------------------
    }
}
