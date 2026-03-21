<?php
namespace NewdichAuth;
use NewdichSchema\Settings;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;
use PDOException;

class Authorization{
    //private $userToken;
    private $user_id;
    private $role;
    private $expirationDate;
    private $authStatus;
    private $authResponse;
    private $jwtSecret = Settings::AUTH_KEY;
    private $jwtKey = Settings::JWT_KEY;
    private $jwthash = Settings::JWT_HASH_ALGORITHM;

    public function __construct(){
        //$this->userToken = $userToken;
        if (!isset($_COOKIE[$this->jwtKey])) {
            $this->authStatus ="failed";
            $this->authResponse ="No Cookie found";
        }

        $headers = new \stdClass();
        $authenticatedKey = $_COOKIE[$this->jwtKey] ?? null;
        if(!$authenticatedKey){
            $this->authStatus ="failed";
            $this->authResponse = $this->jwtKey ." not found in cookie";
        }
            
        try{
            $decodeToken = JWT::decode($authenticatedKey, new Key($this->jwtSecret, $this->jwthash), $headers);
            $this->user_id = $decodeToken->user_id;
            $this->role = $decodeToken->role;
            //$this->expirationDate = $decodeToken->exp;
            $this->authStatus ="success";
            $this->authResponse = [
                "user_id"=>$this->user_id,
                "role"=>$this->role
            ];
        }
        catch(ExpiredException $e){
            $this->authStatus ="failed";
            $this->authResponse ="Authorization token has expired, please relogin";
        }
        catch(Exception $e){
            $this->authStatus ="failed";
            $this->authResponse = $e->getMessage();
        }
    }

    public function authorize(){
        return json_encode(array("status"=>$this->authStatus, "response"=>$this->authResponse), JSON_PRETTY_PRINT);
    }

}
?>