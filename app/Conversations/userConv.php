<?php

namespace App\Conversations;

use App\people;
use App\user_notice;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Telegram\TelegramDriver;

class userConv extends Conversation
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

    public function my_list()
    {

        $user = $this->bot->getUser();
        $this->adminMessage("لیست من: "."\n\n".$user->getFirstName()." ".$user->getLastName());
        $s = ['9','10'];
        $userInfo = people::where('chat_id', '=', $user->getId())->first();
        if (isset($userInfo['id'])) {
            $notices = user_notice::where('user_id', $userInfo['id'])->whereIn('status', $s)->get();
            if(count($notices)>=1) {
                foreach ($notices as $item) {
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
                    $message = $item['text'] . "\n\n ------------------ \n";
                    $message .= "\n" . "زمان ارسال: " . jdate("Y-m-d H:i:s", strtotime($item['created_at']), '', '', 'en');
                    $message .= "\n" . "وضعیت: " . $status;
                    $message .= "\n" . "نمایش نام: " . $show . "\n .";
                    $question = Question::create($message)
                        ->callbackId('check_message_'.$item['id'])
                        ->addButtons([
                            Button::create('حذف')->value('delete_' . $item['id']),
                            Button::create('ویرایش')->value('edit_' . $item['id']),
                        ]);
                    $this->ask($question, function (Answer $answer) {
                        if ($answer->isInteractiveMessageReply()) {
                            $infoCallBack = explode('_', $answer->getValue());
                            $action = $infoCallBack[0];
                            $id = $infoCallBack[1];
                            $this->notice_id = $id;
                            $info = user_notice::find($id);
                            $result = '';
                            if ($action == 'delete') {
                                $result = "متن ارسالی شما: \n" . $info['text'];
                                $result .= "\n\n" . "از سیستم حذف گردید." . "\n\n .";
                                $info->status = 0;
                                $info->save();
                            } elseif ($action == "edit") {
                                return $this->editText();
                            }
                            $this->userMessage($result, $info['userInfo']['chat_id']);
                            $this->adminMessage("حذف متن." . "\n\n" . $result . "\n" . $info['userInfo']['first_name'] . "\n" . $info['userInfo']['last_name'] . "\n\n .");
                        }
                    });
                }
            }else{
                $this->say("هنوز متنی از شما در سیستم نداریم!");
            }
        }
    }

    public function editText()
    {

        $this->ask('لطفاً متن جدید را وارد نمایید', function (Answer $answer) {
            $this->text = $answer->getText();
            $notice = user_notice::find($this->notice_id);
            if (isset($notice['id'])) {
                $notice->text = $this->text;
                $notice->status = 9;
                $notice->save();
                $this->say("متن ارسالی شما با شماره پیگیری $notice->id به موفقیت در سیستم ثبت گردید. بعد از بررسی، نتیجه به اطلاع شما خواهد رسید... \n\n با سپاس" . "\n\n .");
                $this->adminMessage("ویرایش متن انجام شد.");
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
        $this->my_list();
    }
}
