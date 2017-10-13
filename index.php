<?php
/**
 * Created by PhpStorm.
 * User: Dmitriy
 * Date: 11.10.2017
 * Time: 19:13
 */
namespace app;
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__."/vendor/autoload.php";

if(true){
    error_reporting(E_ALL & ~(E_NOTICE | E_USER_NOTICE | E_DEPRECATED));
    ini_set('display_errors', 1);
}
//$events = Parcer::getEvent(76791);
//var_dump($events);


$bot = new Bot();

$bot->registerCommands(ListCommands::class, ListCommands::getAvailableCommands());
$bot->registerEvents(ListCommands::class, ListCommands::getAvailableEvents());
$bot->run();
