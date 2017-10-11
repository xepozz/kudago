<?php
/**
 * Created by PhpStorm.
 * User: Dmitriy
 * Date: 11.10.2017
 * Time: 18:56
 */
namespace app;

use TelegramBot;
use TelegramBot\Api\Client;
use TelegramBot\Api\BotApi;

class Bot
{
    private $config;
    private $bot;
    protected $webHook;

    public function __construct($config) {
        $this->config = $config;
        $this->bot = new BotApi($this->config['token']);
        $this->webHook = $this->bot->getUpdates();
    }
    public function index()
    {
        try {
            $bot = new Client($this->config['token']);
            $bot->command('devanswer', function ($message) use ($bot) {
                preg_match_all('/{"text":"(.*?)",/s', file_get_contents('http://devanswers.ru/'), $result);
                $bot->sendMessage($message->getChat()->getId(),
                    str_replace("<br/>", "\n", json_decode('"' . $result[1][0] . '"')));
            });
            $bot->command('test', function ($message) use ($bot) {
                $bot->sendMessage($message->getChat()->getId(), file_get_contents('http://qaanswers.ru/qwe.php'));
            });
            $bot->run();
        } catch (\TelegramBot\Api\Exception $e) {
            $e->getMessage();
        }
    }
    public function start()
    {

        $bot->sendMessage($chatId, $messageText);
    }
    public function help()
    {
        $bot->sendMessage($chatId, "this is help message");
    }
}