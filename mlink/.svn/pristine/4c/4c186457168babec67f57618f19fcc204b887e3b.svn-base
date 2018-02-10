<?php
class RedisManage
{
    private static $_instance = null; //静态实例
    
    private function __construct(){} //私有的构造方法
    
    //获取静态实例
    public static function getRedis(){
        if(!(self::$_instance instanceof \Redis)){
            self::$_instance = new \Redis();
            self::$_instance->pconnect(REDIS_HOST,REDIS_PORT);
        }
        return self::$_instance;
    }
    
    private function __clone(){} //禁止clone
}
?>