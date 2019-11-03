<?php

namespace App\Conversations;

use App\user_notice;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Telegram\TelegramDriver;

class adminConverstation extends Conversation
{

    public function userMessage($message, $chatId = [])
    {
        $bot = app('botman');
        $bot->say($message, $chatId, TelegramDriver::class);
    }

    public function adminMessage($message)
    {
        $bot = app('botman');
        $bot->say($message, env('ADMIN_ID'), TelegramDriver::class);

    }

    public function listNotice()
    {
        $notice = user_notice::where('status', '=', 9)->get();

        $message = '';
        if (count($notice) >= 1) {
            foreach ($notice as $item) {
                $status = "منتظر بررسی";
                if ($item['status'] == "10") {
                    $status = 'تایید شده';
                } elseif ($item['status'] == "0") {
                    $status = 'حذف شده';
                };
                $show = "نمایش";
                if ($item['show'] == 0) {
                    $show = "عدم نمایش";
                }
                $message = $item['text'] . "\n\n ------------------ \n" . "ارسال کننده: " . $item['userInfo']['first_name'] . " " . $item['userInfo']['last_name'];
                $message .= "\n" . "زمان ارسال: " . jdate("Y-m-d H:i:s", strtotime($item['created_at']), '', '', 'en');
                $message .= "\n" . "وضعیت: " . $status;
                $message .= "\n" . "نمایش نام: " . $show . "\n .";
                $question = Question::create($message)
                    ->callbackId('check_message')
                    ->addButtons([
                        Button::create('تایید')->value('accept_' . $item['id']),
                        Button::create('عدم تایید')->value('reject_' . $item['id']),
                    ]);
                $this->ask($question, function (Answer $answer) {
                    if ($answer->isInteractiveMessageReply()) {
                        $infoCallBack = explode('_', $answer->getValue());
                        $action = $infoCallBack[0];
                        $id = $infoCallBack[1];
                        $this->notice_id = $id;
                        $info = user_notice::find($id);
                        $result = '';
                        if ($action == 'accept') {
                            $result = "متن ارسالی شما: \n" . $info['text'];
                            $result .= "\n\n" . "مورد تایید قرار گرفت." . "\n\n .";
                            $info->status = 10;
                            $info->save();
                        } elseif ($action == "reject") {
                            return $this->askDes();
                        }
                        $this->userMessage($result, $info['userInfo']['chat_id']);
                    }
                });
            }
        } else {
            $this->say('متنی جهت بررسی وجود ندارد.');
        }
    }

    public function askDes()
    {
        $this->ask('لطفاً دلیل عدم تایید را عنوان فرمایید.', function (Answer $answer) {
            $this->text = $answer->getText();
            $notice = user_notice::find($this->notice_id);
            if (isset($notice['id'])) {
                $notice->description = $this->text;
                $notice->status = 0;
                $notice->save();
                $result = "متن ارسالی شما به دلیل: " . "\n\n";
                $result .= $this->text . "\n\n";
                $result .= "مورد تایید قرار نگرفت!" . "\n\n .";
                $this->userMessage($result, $notice['userInfo']['chat_id']);
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
        $this->listNotice();
    }
}
