<?php
/**
 * Created by PhpStorm.
 * User: Dmitriy
 * Date: 11.10.2017
 * Time: 19:13
 */
namespace app;

use \app\Bot;
require_once "vendor/autoload.php";
require_once "config.inc";

$bot = new Bot($config);
var_dump($bot);