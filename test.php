<?php
/**
 * Created by PhpStorm.
 * User: Dmitriy
 * Date: 11.10.2017
 * Time: 21:50
 */
require_once "vendor/autoload.php";
try {
    $bot = new \TelegramBot\Api\Client('400381706:AAHWYH6OvxGAA4MPmwY-ZOZD7r9Sck-rwiE');
    $bot->command('devanswer', function ($message) use ($bot) {
        preg_match_all('/{"text":"(.*?)",/s', file_get_contents('http://devanswers.ru/'), $result);
        $bot->sendMessage($message->getChat()->getId(),
            str_replace("<br/>", "\n", json_decode('"' . $result[1][0] . '"')));
    });
    $bot->command('qaanswer', function ($message) use ($bot) {
        $bot->sendMessage($message->getChat()->getId(), file_get_contents('http://qaanswers.ru/qwe.php'));
    });
    $bot->run();
} catch (\TelegramBot\Api\Exception $e) {
    $e->getMessage();
}