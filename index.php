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
$debug = false;
if($debug){
    error_reporting(E_ALL & ~(E_NOTICE | E_USER_NOTICE | E_DEPRECATED));
    ini_set('display_errors', 1);
}
$bot = new Bot($debug);

$bot->registerCommands(ListCommands::class, ListCommands::getAvailableCommands());
$bot->registerEvents(ListCommands::class, ListCommands::getAvailableEvents());
$bot->run();
