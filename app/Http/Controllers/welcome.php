<?php

namespace App\Http\Controllers;

use App\Conversations\adminConverstation;
use App\Conversations\userConv;
use App\people;
use App\user_notice;
use BotMan\BotMan\BotMan;
use BotMan\Drivers\Telegram\TelegramDriver;

class welcome extends Controller
{
    /**
     * Place your BotMan logic here.
     */
    public function handle()
    {
        $botman = app('botman');

        $botman->listen();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tinker()
    {
        return view('tinker');
    }

    /**
     * Loaded through routes/botman.php
     * @param  BotMan $bot
     */
    public function welcome(BotMan $bot)
    {
        $bot->startConversation(new \App\Conversations\welcome());
    }

    public function admin_list(BotMan $bot)
    {
        $bot->startConversation(new adminConverstation());
    }

    public function about(BotMan $bot)
    {
        $messages ="ارتباط با برنامه نویس:"."\n\n";
        $messages .="\xF0\x9F\x8C\x8D	"." https://mkardgar.com"."\n\n";
        $messages .="\xF0\x9F\x93\xAE	"." mk.kardgar@gmail.com"."\n\n";
        $messages .="\xF0\x9F\x93\xB1	"." +98 912 684 2114"."\n\n";
        $bot->reply($messages);
    }


    public function my_text(BotMan $bot)
    {
        $bot->startConversation(new userConv());
    }
}
