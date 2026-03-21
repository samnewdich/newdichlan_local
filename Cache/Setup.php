<?php
namespace NewdichCache;
use NewdichSchema\Settings;
class Setup{
    private $redisServerIp = Settings::REDIS_SERVER_IP;
    private $redisServerPort = Settings::REDIS_SERVER_PORT;
    private $redisServerPassword = Settings::REDIS_AUTH_PASSWORD;
    public function setCache($key, $dataToCache){
        try{
            $redis = new Redis();
            $redis->connect($redisServerIp, $redisServerPort);
            $redis->auth($redisServerPassword);
            return $redis;
        }
        catch(Exception $e){
            return json_encode(array("status"=>"failed", "response"=>$e->getMessage()), JSON_PRETTY_PRINT);
        }
    }
}
?>