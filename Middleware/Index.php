<?php
namespace NewdichMiddleware;

class Index{
    public function __construct(){

    }

    public function getIp(){
        if(!empty($_SERVER["HTTP_CLIENT_IP"])){
            return $_SERVER["HTTP_CLIENT_IP"];
        }
        elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        else{
            return $_SERVER["REMOTE_ADDR"];
        }
    }


    public function hashData($data){
        $hash = password_hash($data, PASSWORD_BCRYPT);
        return $hash;
    }

    public function verifyHash($data, $hash){
        return password_verify($data, $hash); //returns a boolean
        
    }

    public function cleanData($data){
        return trim(htmlspecialchars($data));
    }

    public function otp(){
        $otp = strtotime(date("Y-m-d H:i:s"));
        $otp = sprintf("%u", crc32($otp.$this->getIp()));
        $otp = substr($otp, 0, 6);
        return $otp;
    }

    public function marchantCode(){
        $otp = strtotime(date("Y-m-d H:i:s"));
        $otp = sprintf("%u", crc32($otp.$this->getIp()));
        $otp = substr($otp, 0, 10);
        return $otp;
    }


    public function apiHeaders()
    {
        if (function_exists('getallheaders')) {
            $headers = array_change_key_case(getallheaders(), CASE_LOWER);
        } else {
            $headers = [];
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $headers[strtolower(str_replace('_', '-', substr($key, 5)))] = $value;
                } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                    $headers[strtolower(str_replace('_', '-', $key))] = $value;
                }
            }
        }

        $host = $headers['host'] ?? '';
        $contentType = $headers['content-type'] ?? '';
        //$authorization = $headers['authorization'] ?? ($headers['x-api-key'] ?? '');
        $authorization = $headers['authorization'] ?? $headers['x-api-key'] ?? $headers['api-key'] ?? '';

        $label = '';
        $apiKey = '';
        if (!empty($authorization)) {
            if (preg_match('/^(Bearer|ApiKey|Token|SecretKey)\s+(\S+)$/i', $authorization, $matches)) {
                $label = $matches[1];
                $apiKey = $matches[2];
            } else {
                $apiKey = $authorization;
            }
        }

        if ($host && $contentType && $apiKey) {
            return json_encode([
                "status" => "success",
                "response" => [
                    "host" => $host,
                    "contentType" => $contentType,
                    "apiKey" => $apiKey,
                    "label" => $label
                ]
            ], JSON_PRETTY_PRINT);
        }

        return json_encode([
            "status" => "failed",
            "response" => "Could not retrieve headers info"
        ], JSON_PRETTY_PRINT);
    }
}
?>