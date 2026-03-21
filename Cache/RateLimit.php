<?php
namespace NewdichCache;
use NewdichCache\Index;

class RateLimit{
    private $limit;
    private $window;
    private $ip;

    public function __construct($limit, $window, $ip){
        $this->limit = $limit;
        $this->window = $window;
        $this->ip = $ip;
    }

    public function process(){
        $key = "rate:".$this->ip;

        $newIndex = new Index();
        $count = $newIndex->setIncrease($key);

        if ($count == 1) {
            $newIndex->setExpire($key, $this->window);
        }

        if ($count > $this->limit) {
            echo json_encode([
                'status' => false,
                'message' => 'Too many requests'
            ]);
            exit;
        }
    }
}
?>