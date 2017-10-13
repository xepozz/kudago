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
    private $config = []; // config
    private $bot; // instance of TelegramBot
    private $debugging; // print information on page
    private $configFile = 'config.json'; // path into config file

    public function __construct(int $debugging = 1)
    {
        $this->loadConfig();
        $this->bot = new Client($this->config->token);
        $this->setHook();
        $this->debugging = $debugging;
    }
    //load config from $configFile
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
            $this->debug($ex->getMessage());
            return 0;
        }
        return 1;
    }
    //save config into $configFile
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
            $this->debug($ex->getMessage());
            return 0;
        }
        return 1;
    }
    //set WebHook
    private function setHook()
    {
        if(file_exists($this->configFile) && (!key_exists("hooked", $this->config) || $this->config->hooked == 0)){
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
    //register commands
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
                $this->debug("error: method " . $function . " not found in " . $class);

            $this->debug("Command $command handled $class::$function(msg, bot)");
        }
    }
    //register events
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
                $this->debug("error: method " . $event . " not found in " . $class);

            $this->debug("Event $event handled $class::$event(update, bot)");
        }
    }
    //register inlineQueries
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
                $this->debug("error: method " . $method . " not found in " . $class);

            $this->debug("InlineQuery $method handled $class::$method(update, bot)");
        }
    }
    //print function if $debugging is true
    private function debug($text)
    {
        if($this->debugging){
            var_dump($text);
            echo '<br/>';
        }
        return false;
    }
    //alias for TelegramBot->run
    public function run()
    {
        return $this->bot->run();
    }
}