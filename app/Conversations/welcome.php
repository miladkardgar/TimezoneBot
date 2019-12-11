<?php

namespace App\Conversations;

use App\people;
use App\user_notice;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Telegram\TelegramDriver;
use Illuminate\Foundation\Inspiring;

class welcome extends Conversation
{

    public function adminMessage($message)
    {
        $bot = app('botman');
        $bot->say($message, env('ADMIN_ID'), TelegramDriver::class);

    }

    public function welcomeMessage()
    {
        $userInfo = $this->bot->getUser();
        $chat_id = '';
        $username = '';
        $first_name = '';
        $last_name = '';
        if ($userInfo->getId()) {
            $chat_id = $userInfo->getId();
        }
        if ($userInfo->getUsername()) {
            $username = $userInfo->getUsername();
        }
        if ($userInfo->getFirstName()) {
            $first_name = $userInfo->getFirstName();
        }
        if ($userInfo->getLastName()) {
            $last_name = $userInfo->getLastName();
        }
        $message = $first_name . " " . $last_name . "خوش آمدید.";
        $message .= "\n\n" . "این ربات نوشته های شما را در حافظه خود ذخیره میکند و به طور تصادفی در بیست و چهار ساعت شبانه روز در کانال اعلام ساعت نمایش میدهد.";
        $message .= "\n\n" . "چنانچه مایل باشید میتوانید انتخاب نمایید نام شما در هنگام اشتراک گزاری متن در کانال اعلام ساعت به نمایش گذاشته شود یا خیر.";
        $message .= "\n\n" . "دقت نمایید متن ارسالی بعد از تایید آدمین سیستم در کانال به اشتراک گذاشته میشود.";
        $message .= "\n\n" . "با سپاس";
        $this->adminMessage("شروع: "."\n\n".$first_name." ".$last_name."\n\n .");
        $question = Question::create($message)
            ->fallback('Unable to ask question')
            ->callbackId('ask_reason')
            ->addButtons([
                Button::create('شروع ارسال متن')->value('text'),
            ]);

        return $this->ask($question, function (Answer $answer) {

            if ($answer->isInteractiveMessageReply()) {
                $userInfo = $this->bot->getUser();
                $user = people::where('chat_id', $userInfo->getId())->first();
                if (!isset($user['id'])) {
                    $chat_id = '';
                    $username = '';
                    $first_name = '';
                    $last_name = '';
                    if ($userInfo->getId()) {
                        $chat_id = $userInfo->getId();
                    }
                    if ($userInfo->getUsername()) {
                        $username = $userInfo->getUsername();
                    }
                    if ($userInfo->getFirstName()) {
                        $first_name = $userInfo->getFirstName();
                    }
                    if ($userInfo->getLastName()) {
                        $last_name = $userInfo->getLastName();
                    }
                    $id = people::create(
                        [
                            'chat_id' => $chat_id,
                            'username' => $username,
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                        ]
                    );
                    $this->adminMessage('کاربر جدید:'."\n\n".$first_name." ".$last_name."\n\n".$username."\n\n".$chat_id."\n\n .");
                    $this->user_id = $id['id'];
                } else {
                    $this->adminMessage('کاربر فعلی:'."\n\n".$user['first_name']." ".$user['last_name']."\n\n".$user['username']."\n\n".$user['chat_id']."\n\n .");
                    $this->user_id = $user['id'];
                }
                if ($answer->getValue() === 'text') {
                    $this->sendText();
                } else {
                    $this->say(Inspiring::quote());
                }
            }
        });

    }

    public function sendText()
    {
        $this->ask('متن مورد نظر را ارسال نمایید.', function (Answer $answer) {
            $this->text = $answer->getText();
            if($this->text!="") {
                $notice = user_notice::create(
                    [
                        'text' => $this->text,
                        'user_id' => $this->user_id,
                        'status' => 9
                    ]
                );
                if (isset($notice['id'])) {
                    $this->notice_id = $notice['id'];
                    $this->showInfo();
                } else {
                    $this->say("خطا در ثبت اطلاعات!");
                    $this->welcomeMessage();
                }
            }
        });
    }

    public function showInfo()
    {
        $question = Question::create("آیا مایل هستید نام شما در هنگام به اشتراک گزاری متن به نمایش گزاشته شود؟")
            ->fallback('Unable to ask question')
            ->callbackId('ask_reason')
            ->addButtons([
                Button::create('نمایش نام من')->value('show'),
                Button::create('عدم نمایش نام من')->value('notShow'),
            ]);

        return $this->ask($question, function (Answer $answer) {

            if ($answer->isInteractiveMessageReply()) {
                $notice = user_notice::find($this->notice_id);
                if ($answer->getValue() === 'show') {
                    $notice->show = 1;
                } else {
                    $notice->show = 0;
                }
                $notice->save();
                $this->adminMessage("متن جدید"."\n"."/p_list");
                $this->say("متن ارسالی شما با شماره پیگیری $notice->id به موفقیت در سیستم ثبت گردید. بعد از بررسی، نتیجه به اطلاع شما خواهد رسید... \n\n با سپاس" . "\n\n .");
            }
        });
    }


    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        //
        $this->welcomeMessage();
    }



}
