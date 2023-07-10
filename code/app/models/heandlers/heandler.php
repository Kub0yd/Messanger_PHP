<?php 
 namespace App\Models;

class DBConn {

    private static $dbData;

    public static function getDbData(){

        self::$dbData = include('../config/db_config.php');

        return self::$dbData;
    }

    public static function connect(){
        
        $data = self::getDbData();
        $db = new \PDO("mysql:host={$data['host']};dbname={$data['db']}", $data['user'], $data['password'],[\PDO::ATTR_ERRMODE => \PDO::ERRMODE_WARNING]);

        return $db;
    }
}

class Functions{

    public function generateCode($length = 6){

        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
        $code = "";
        $clen = strlen($chars) - 1;
        while (strlen($code) < $length) {
                $code .= $chars[mt_rand(0,$clen)];
        }
        return $code;
        
    }
}
