<?php
namespace NewdichCache;
use NewdichCache\Setup;
class Index{
    private function conRedis(){
        $newSetup = new Setup();
        return $newSetup;
    }

    public function setCache($key, $dataToCache){
        try{
            $this->conRedis()->set($key, $dataToCache);
            return json_encode(array("status"=>"success", "response"=>"successfully cached"), JSON_PRETTY_PRINT);
        }
        catch(Exception $e){
            return json_encode(array("status"=>"failed", "response"=>$e->getMessage()), JSON_PRETTY_PRINT);
        }
    }


    public function setExpireCache($key, $dataToCache, $time){
        try{
            $time = (int) $time;
            $this->conRedis()->setex($key, $time, $dataToCache);
            return json_encode(array("status"=>"success", "response"=>"successfully cached with expiration time"), JSON_PRETTY_PRINT);
        }
        catch(Exception $e){
            return json_encode(array("status"=>"failed", "response"=>$e->getMessage()), JSON_PRETTY_PRINT);
        }
    }


    public function setIncrease($key){
        try{
            $this->conRedis()->incr($key);
            $arrayOut = array(
                "message"=>"successfully increased",
                "value"=> $this->conRedis()->get($key)
            );
            return json_encode(array("status"=>"success", "response"=>$arrayOut), JSON_PRETTY_PRINT);
        }
        catch(Exception $e){
            return json_encode(array("status"=>"failed", "response"=>$e->getMessage()), JSON_PRETTY_PRINT);
        }
    }


    public function setDecrease($key){
        try{
            $this->conRedis()->decr($key);
            $arrayOut = array(
                "message"=>"successfully decreased",
                "value"=> $this->conRedis()->get($key)
            );
            return json_encode(array("status"=>"success", "response"=>$arrayOut), JSON_PRETTY_PRINT);
        }
        catch(Exception $e){
            return json_encode(array("status"=>"failed", "response"=>$e->getMessage()), JSON_PRETTY_PRINT);
        }
    }


    public function getCache($key){
        try{
            $dataretrieved = $this->conRedis()->get($key);
            return json_encode(array("status"=>"success", "response"=>$dataretrieved), JSON_PRETTY_PRINT);
        }
        catch(Exception $e){
            return json_encode(array("status"=>"failed", "response"=>$e->getMessage()), JSON_PRETTY_PRINT);
        }
    }



    public function setExpire($key, $window){
        try{
            $window = (int) $window;
            $this->conRedis()->expire($key, $window);
            return json_encode(array("status"=>"success", "response"=>"successfully expired"), JSON_PRETTY_PRINT);
        }
        catch(Exception $e){
            return json_encode(array("status"=>"failed", "response"=>$e->getMessage()), JSON_PRETTY_PRINT);
        }
    }
}
?>