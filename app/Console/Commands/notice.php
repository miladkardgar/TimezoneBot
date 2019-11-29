<?php

namespace App\Console\Commands;

use App\event;
use App\people;
use App\User;
use App\user_notice;
use BotMan\Drivers\Telegram\TelegramDriver;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class notice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notice:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'every one hour send a notice to timezone bot';

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
        $botman = app('botman');
        $adminID = env('ADMIN_ID');
        $zone = '';
        $hour = date("g", time());
        $a = date("A", time());
        $hCheck = date("H", time());
        $icon = '';
        switch ($hour) {
            case 1:
                $icon = "\xF0\x9F\x95\x90 ";
                break;
            case 2:
                $icon = "\xF0\x9F\x95\x91 ";
                break;
            case 3:
                $icon = "\xF0\x9F\x95\x92 ";
                break;
            case 4:
                $icon = "\xF0\x9F\x95\x93 ";
                break;
            case 5:
                $icon = "\xF0\x9F\x95\x94 ";
                break;
            case 6:
                $icon = "\xF0\x9F\x95\x95 ";
                break;
            case 7:
                $icon = "\xF0\x9F\x95\x96 ";
                break;
            case 8:
                $icon = "\xF0\x9F\x95\x97 ";
                break;
            case 9:
                $icon = "\xF0\x9F\x95\x98 ";
                break;
            case 10:
                $icon = "\xF0\x9F\x95\x99 ";
                break;
            case 11:
                $icon = "\xF0\x9F\x95\x9A ";
                break;
            case 12:
                $icon = "\xF0\x9F\x95\x9B ";
                break;
        }

        if ($hour > 0 && $hour <= 6 && $a == "AM") {
            $zone = "بامداده";
        } elseif ($hour > 6 && $hour <= 11 && $a == "AM") {
            $zone = "صبحه";
        } elseif ($hour == 12 && $a == "AM") {
            $zone = "ظهره";
        } elseif ($hour > 0 && $hour <= 4 && $a == "PM") {
            $zone = "بعد از ظهره";
        } elseif ($hour > 4 && $hour <= 6 && $a == "PM") {
            $zone = "عصره";
        } elseif ($hour > 6 && $hour <= 11 && $a == "PM") {
            $zone = "شبه";
        }
        if ($hour == 12 && $a == "AM") {
            $zone = "شبه";
        }
        if ($hour == 12 && $a == "PM") {
            $zone = "ظهره";
        }

        $text = $icon . "الان " . $hour . " " . $zone;
        $finalText = $text . "\n";
        if ($hCheck == 8) {
            $d = jdate("d", time(), '', '', 'en');
            $m = jdate("m", time(), '', '', 'en');
            $getEvents = event::where(
                [
                    ['dayPr', '=', $d],
                    ['mountPr', '=', $m],
                ]
            )->get();
            $finalText .= "\xF0\x9F\x93\x85	" . "امروز " . jdate("l d F Y") . "\n\n";
            if (sizeof($getEvents) >= 1) {
                $finalText .= "رویداد ها:" . "\n\n";
                foreach ($getEvents as $getEvent) {
                    $finalText .= "\xF0\x9F\x94\xB5	" . $getEvent['occasion'] . "\n";
                }
            }
        }
        if ($hCheck > 8 && $hCheck < 23) {
            $getNo = \App\notice::all()->random();
            $getUserNo = user_notice::with('userInfo')->where('status', 10)->get()->random();

            $created = new Carbon($getUserNo['updated_at']);
            $now = Carbon::now();
            if ($created->diff($now)->h > 4) {
                $finalText .= "\n\n" . $getUserNo['text'];
                if ($getUserNo['show'] == 1) {
                    $finalText .= "\n\n" . $getUserNo['userInfo']['username'];
                }
                $getUserNo->update(
                    [
                        'count_show' => DB::raw('count_show+1')
                    ]
                );
                $getUserNo->save();
            } else {
                $getNo->update(
                    [
                        'count' => DB::raw('count+1')
                    ]
                );
                $getNo->save();
                $finalText .= "\n\n" . $getNo['text'];
            }
            $finalText .= "\n .";
        }
        $botman->say($finalText, "-1001332329957", TelegramDriver::class, ['parse_mode' => 'HTML']);
        $botman->say($finalText, $adminID, TelegramDriver::class, ['parse_mode' => 'HTML']);
    }
}
