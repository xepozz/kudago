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
    private $config = [];
    private $bot;
    private $webHook;
    private $configFile = 'config.json';

    public function __construct()
    {
//        var_dump(get_class_methods(__CLASS__));
        $this->loadConfig();
//        var_dump($this);
        $this->bot = new Client($this->config->token);
        $this->setHook();
        $bot = $this->bot;
//        $this->registerCommands(["index"]);
    }
    private function loadConfig($file = null)
    {
        try {
            $this->configFile = ($file ? $file : $this->configFile);

            if(!file_exists($this->configFile))
                file_put_contents(__DIR__.'/'.$this->configFile, json_encode([
                    'token' => 'TOKEN',
                ]));
            $data = file_get_contents($this->configFile);
            $this->config = json_decode($data);
        }catch (\Exception $ex){
            echo $ex->getMessage();
            return 0;
        }
        return 1;
    }
    private function saveConfig()
    {
        try {
            if(!file_exists($this->configFile))
                file_put_contents(__DIR__.'/'.$this->configFile, json_encode([
                    'token' => 'TOKEN',
                ]));

            $data = json_encode($this->config);
            file_put_contents($this->configFile, $data);
        }catch (\Exception $ex){
            echo $ex->getMessage();
            return 0;
        }
        return 1;
    }

    private function setHook()
    {
        if(file_exists($this->configFile) && (!key_exists("hooked", $this->config) || $this->config->hooked == 0)){
            /**
             * файл registered.trigger будет создаваться после регистрации бота.
             * если этого файла нет значит бот не зарегистрирован
             */
            $page_url = "https://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]; // текущая страница
            $crt = new \CURLFile("ssl/bot.crt"); // самоподписный сертификат
            $result = $this->bot->setWebhook($page_url, $crt);
            if($result){
                $this->config->hooked = 1;
                if($this->saveConfig())
                    return 1;
            }
        }
        return 0;
    }
    public function registerCommands($class, $methods)
    {
        $bot = $this->bot;
        foreach ($methods as $alias => $function)
        {
            $command = !is_int($alias) ? $alias : $function;
            if(method_exists($class, $function))
                $this->bot->command(strtolower($command), function($msg) use ($bot, $class, $function){
                    $class::$function($msg, $bot);
                });
            else
                echo "error: method " . $function . " not found in " . $class . "<br>";

            echo "Command $command handled $class::$function(msg, bot)<br>";
        }
    }

    public function registerEvents($class, $methods)
    {
        $bot = $this->bot;
        foreach ($methods as $event => $checker)
        {
            if(is_string($checker)){
                $event = $checker;
                $checker = null;
            }
            if(method_exists($class, $event))
                $this->bot->on(function($update) use ($bot, $class, $event){
                    $class::$event($update, $bot);
                }, $checker);
            else
                echo "error: method " . $event . " not found in " . $class . "<br>";

            echo "Event $event handled $class::$event(update, bot)<br>";
        }
    }
    public function registerInlineQueries($class, $methods)
    {
        $bot = $this->bot;
        foreach ($methods as $method)
        {
            if(method_exists($class, $method))
                $this->bot->inlineQuery(function($inlineQuery) use ($bot, $class, $method){
                    $class::$method($inlineQuery, $bot);
                });
            else
                echo "error: method " . $method . " not found in " . $class . "<br>";

            echo "InlineQuery $method handled $class::$method(update, bot)<br>";
        }
    }
    public function run()
    {
        return $this->bot->run();
    }
}