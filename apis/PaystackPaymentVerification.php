<?php
namespace NewdichApis;
use NewdichDto\AnsofraDto;
use NewdichMiddleware\Index;
use NewdichSchema\Settings;

class PaystackPaymentVerification{
    private $dto;
    private $middle;
    private $paystackSecretKey = Settings::PAYSTACK_SECRET_KEY;
    private $paystackVerififactionLink = Settings::PAYSTACK_VERIFICATION_LINK;
    public function __construct(AnsofraDto $dto, Index $middle){
        $this->dto = $dto;
        $this->middle = $middle;
    }

    public function process(){
        $reference = $this->middle->cleanData($this->dto->reference);
        $secretKey = $this->paystackSecretKey;
        $url = $this->paystackVerififactionLink.$reference;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $secretKey"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);
        if ($result['data']['status'] === "success") {
            return json_encode(["status"=>"success", "response"=>"Payment verified successfully"], JSON_PRETTY_PRINT);
        } else {
            return json_encode(["status"=>"failed", "response"=>"Payment verification failed"], JSON_PRETTY_PRINT);
        }
    }
}
?>