<?php
/**
 * Created by PhpStorm.
 * User: Dmitriy
 * Date: 13.10.2017
 * Time: 19:04
 */

namespace app;
define("SP", "/");

class Parcer
{
    public static $api = 'https://kudago.com/public-api/v1.3';
    public static $_template = 'api/method/?query';
    public static function getEvent($id/*, $slug = null, $count = 5, $offset = null*/)
    {
        $data = self::get('events/'.$id);
        return $data;
    }
    public static function getEvents($slug, $count = 5, $offset = null)
    {
        $data = self::get('events', [
            'categories' => $slug,
            'page_size' => $count,
            'page' => ($offset/$count)+1,
        ]);
        return $data;
    }
    public static function getEventCategories()
    {
        $data = self::get('event-categories');
        return $data;
    }
    public static function get($page, $query = null, $decode = true, $asArray = true)
    {
        $data = self::_load(self::_link($page, $query));
        return $decode ? json_decode($data, $asArray) : $data;
    }
    protected static function _link($method, $query = null)
    {
        $query = $query ? self::_buildQuery($query) : $query;
        $link = self::$_template;
        $link = str_replace('api', self::$api, $link);
        $link = str_replace('method', $method, $link);
        $link = str_replace('query', $query, $link);
//        echo $link;
        return $link;
//        return self::$api . SP . $method . SP. $query;
    }
    private static function _buildQuery($query)
    {
        return http_build_query($query);
    }
    private static function _load($url)
    {
        try{
            return file_get_contents($url);
        }catch (\Exception $exception)
        {
            echo $exception->getMessage();
            exit(1);
        }
    }
}