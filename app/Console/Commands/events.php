<?php

namespace App\Console\Commands;

use App\event;
use BotMan\Drivers\Telegram\TelegramDriver;
use Illuminate\Console\Command;

class events extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get day event';

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
    public function handle()
    {
        //

        $curl = curl_init();
        $dPe = jdate("d", time(), '', '', 'en');
        $dEn = date("d");
        $mPe = jdate("m", '', '', '', 'en');
        $mEn = date("m");
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://farsicalendar.com/api/sh,wc/$dPe,$dEn/$mPe,$mEn",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $botman = app('botman');
        $adminID = env('ADMIN_ID');
        $adminText = 'لیست مناسبت های امروز: ' . "\n";
        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $res = json_decode($response, true);
            foreach ($res['values'] as $value) {
                $adminText .= $value['occasion'] . "\n";
                $botman->say($value['occasion'], $adminID, TelegramDriver::class, ['parse_mode' => 'HTML']);
                $check = event::where('e_id', $value['id'])->first();
                if (!$check['id']) {
                    notice::create(
                        [
                            "e_id" => $value['id'],
                            "year" => $value['year'],
                            "dayoff" => $value['dayOff'],
                            "type" => $value['type'],
                            "category" => $value['category'],
                            "occasion" => $value['occasion'],
                            "datetime" => date("Y-m-d H:i:s"),
                            "dayPr" => $dPe,
                            "mountPr" => $mPe,
                            "dayEn" => $dEn,
                            "mountEn" => $mEn
                        ]
                    );
                }
            }
            $adminText .= "\n" . $dPe . "-" . $mPe . "\n .";
            $botman->say($adminText, $adminID, TelegramDriver::class, ['parse_mode' => 'HTML']);
        }
    }
}
