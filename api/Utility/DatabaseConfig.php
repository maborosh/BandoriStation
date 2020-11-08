<?php


namespace BS_API\Utility;


use PDO;
use Redis;

class DatabaseConfig
{
    public static function mysqlDBHelper($db_name)
    {
        $dbh = new PDO('mysql:host=' . GlobalConfig::DATABASE_SERVER_IP .
            ';dbname=' . $db_name, GlobalConfig::DATABASE_USERNAME, GlobalConfig::DATABASE_PASSWORD);
        $dbh->query('set names utf-8');
        $dbh->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbh;
    }

    public static function redisConnect()
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1');
        return $redis;
    }
}