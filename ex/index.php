<?php

require_once "vendor/autoload.php";

try {
    $bot = new \TelegramBot\Api\Client('400381706:AAHWYH6OvxGAA4MPmwY-ZOZD7r9Sck-rwiE');

    $bot->on(function($update) use ($bot, $callback_loc, $find_command){
        $callback = $update->getCallbackQuery();
        $message = $callback->getMessage();
        $chatId = $message->getChat()->getId();
        $data = $callback->getData();

        if($data == "data_test"){
            $bot->answerCallbackQuery( $callback->getId(), "This is Ansver!",true);
        }
        if($data == "data_test2"){
            $bot->sendMessage($chatId, "Это ответ!");
            $bot->answerCallbackQuery($callback->getId()); // можно отослать пустое, чтобы просто убрать "часики" на кнопке
        }

    }, function($update){
        $callback = $update->getCallbackQuery();
        if (is_null($callback) || !strlen($callback->getData()))
            return false;
        return true;
    });

    $bot->command('test', function ($message) use ($bot) {
        $bot->sendMessage($message->getChat()->getId(), 'test');
    });

//    $bot->run();


} catch (\TelegramBot\Api\Exception $e) {
    $e->getMessage();
}
