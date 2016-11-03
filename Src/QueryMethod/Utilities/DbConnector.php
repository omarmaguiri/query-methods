<?php
namespace QueryMethod\Utilities;

use \PDO;

class DbConnector
{
    private static $pdo;
    /**
     * DbConnector constructor.
     */
    private function __construct(){}

    public static function getPDO(){
        if (is_null(static::$pdo)){
            $connector = new static();
            $params = $connector->getConnectionParams();
            $dsn = $params['driver'].":"."host"."=".$params['host'].";"."dbname"."=".$params['dbName'].";"."charset"."=".$params['charset'];
            $username = $params['username'];
            $password = $params['password'];
            $connector::$pdo = new PDO($dsn, $username, $password);
            //echo "[dsn:".$dsn."],[username:".$username."][password:".$password."];";
            return $connector::$pdo;
        }
        return static::$pdo;
    }
    private function getConnectionParams(){
//        return Configuration::getConfig('database');
        $config = [
            "driver" => "mysql",
            "host" => "localhost",
            "dbName" => "test",
            "charset" => "utf8",
            "username" => "root",
            "password" => "",
        ];
        return $config;
    }
}
