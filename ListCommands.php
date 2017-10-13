<?php
/**
 * Created by PhpStorm.
 * User: Dmitriy
 * Date: 12.10.2017
 * Time: 0:12
 */

namespace app;

use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\MessageEntity;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\Update;
use TelegramBot\Api\Types\Inline\InlineQuery;

class ListCommands extends \app\Bot
{
    // список доступных для регистрации комманд
    public static function getAvailableCommands()
    {
        return [
            "start",
            '?' => "help",
            "help",
            "eventCategories",
        ];
    }
    // список доступных для регистрации евентов
    public static function getAvailableEvents()
    {
        return [
            'onImg' => function($update){
                $callback = $update->getCallbackQuery();
                if (is_null($callback) || !strlen($callback->getData()))
                    return false;
                return true;
            },
            'onEvent' => function($message){
                return true; // когда тут true - команда проходит
            },
        ];
    }
    // список доступных для регистрации инлайнов (пока не работает)
    public static function getAvailableInlines() {
        return [
            'iqEvents'
        ];
    }

    //commands
    public static function index(Message $msg, Client $bot)
    {
        $bot->sendMessage($msg->getChat()->getId(), "this is index method in ListCommands");
    }
    public static function event(Message $msg, Client $bot, $id, $slug = null, $offset = 0, $count = 10)
    {
        $event = Parcer::getEvent($id);
        $event['description'] = preg_replace('/<\/?p>/i', '', $event['description']);
        $eventTitles = [
            '<b>'. $event['title'] . '</b>',
            $event['description']
        ];
        if(key_exists('site_url', $event) && key_exists("short_title", $event))
            array_push($eventTitles, 'Сайт: <a href="' . $event['site_url'] . '">' . $event['short_title'] . '</a>');
        $buttons = [];
        /*if(key_exists('results', $events))
        {
            $i = $offset;
            $eventTitles = array_map(function($array) use (&$i)
            {
                $i++;
                return $i . '. ' . $array['title'] . '';
            }, $events['results']);
        }*/
        $buttons[] = [
            'text' => "Назад",
            'callback_data' => 'events offset ' . (int)$offset . ' ' .$slug . ' ' . $count,
        ];
        $keyboard = new InlineKeyboardMarkup([
            $buttons
        ]);
        $bot->sendMessage($msg->getChat()->getId(), join("\n", $eventTitles), "HTML", null, null, $keyboard);
    }
    public static function events(Message $msg, Client $bot, $slug, $offset = 0, $count = 10)
    {
        $events = Parcer::getEvents($slug, $count, $offset);
        $eventTitles = [];
        $buttons = [];
        if(key_exists('results', $events))
        {
            $i = $offset;
            $eventTitles = array_map(function($array) use (&$i, &$buttons, $slug, $count, $offset)
            {
                $i++;
                $buttons[0][] = [
                    'text' => $i,
                    'callback_data' => 'event id ' . $array['id'] . ' '. $slug . ' ' . $offset . ' ' . $count
                ];
                return $i . '. ' . $array['title'] . '';
            }, $events['results']);
        }
        if($offset > 0) // убрать кнопку с первой страницы
            $buttons[1][] = [
                'text' => "Назад",
                'callback_data' => 'events offset ' . (int)($offset - $count) . ' ' .$slug . ' ' . $count,
            ];
        $offset += $count;
        if($offset < $events['count']) // убрать кнопку с последней страницы
            $buttons[1][] = [
                'text' => "Вперед",
                'callback_data' => 'events offset ' . (int)$offset . ' ' . $slug . ' ' . $count,
            ];
        $keyboard = new InlineKeyboardMarkup($buttons, true, true);
        $bot->sendMessage($msg->getChat()->getId(), join("\n", $eventTitles), "Markdown", null, null, $keyboard);
    }
    public static function eventCategories(Message $msg, Client $bot)
    {
        $categories = Parcer::getEventCategories();
        $buttons = [];
        if(is_array($categories))
        {
            $i = 0;
            $message = array_map(function($array) use (&$i, &$buttons)
            {
                $i++;
                $buttons[intdiv($i, 10)][] = ['text' => $i, 'callback_data' => 'events page ' . $array['slug']];
                return $i . '. ' . $array['name'] . '';
            }, $categories);
        }
        array_unshift($message, '*Выберите категорию из списка:*');
        $keyboard = new InlineKeyboardMarkup($buttons, true, true);
        $bot->sendMessage($msg->getChat()->getId(), join("\n", $message), "Markdown", null, null, $keyboard);
    }
    public static function start(Message $msg, Client $bot)
    {
        $bot->sendMessage($msg->getChat()->getId(), "Привет. Этот бот - тестовое задания. Сервер парсит данные компании kudago и выдает их в удобной форме сюда.");
        self::help($msg, $bot);
    }
    public static function help(Message $msg, Client $bot)
    {
        $lines = [];
        $lines[] = "Список доступных команд:";
        $lines[] = '/start - Начало работы с ботом.';
        $lines[] = '/help - Вывод текущей справки(алиас /?).';
        $lines[] = '/eventcategories - Вывод доступных категорий.';
        $bot->sendMessage($msg->getChat()->getId(), join("\n", $lines));
    }
    //events
    public static function onEvent(Update $update, Client $bot)
    {
        $callback = $update->getCallbackQuery();
        $message = $callback->getMessage();
        $messageText = $message->getText();
        $replyId = $message->getChat()->getId();
        $callbackText = $update->getCallbackQuery()->getData();
        $data = explode(" ", $callbackText);
//        $bot->sendMessage($replyId, $callbackText);

        if(mb_stripos($callbackText, "events offset") !== false){
            // 0 - events_page, 1 - offset, 2 - offset number, 3 - slug, 4 - count
            self::events($message, $bot, $data[3], $data[2], $data[4]);
        }
        if(mb_stripos($callbackText, "events page") !== false){
             // 0 - events_page, 1 - page, 2 - slug
            self::events($message, $bot, $data[2]);
        }
        if(mb_stripos($callbackText, "event id") !== false){
             // 0 - events_page, 1 - id, 2 - id number, 3 - slug, 4 - offset, 5 - count
            self::event($message, $bot, $data[2], $data[3], $data[4], $data[5]);
        }
        $bot->answerCallbackQuery($callback->getId());
    }
}