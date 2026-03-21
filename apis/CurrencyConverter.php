<?php
namespace NewdichApis;
use NewdichDto\AnsofraDto;
use NewdichMiddleware\Index;
use NewdichSchema\Settings;

class CurrencyConverter{
    private $dto;
    private $middleware;
    private $ExchangeRateApiKey = Settings::EXCHANGERATE_APIKEY;
    private $ExchangeRateApiLink = Settings::EXCHANGERATE_LINK;
    public function __construct(AnsofraDto $dto){
        $this->dto = $dto;
    }

    public function process(){
        //This api uses https://www.exchangerate-api.com/ for currency conversion
        $yourExchangeRateApiKey = $this->ExchangeRateApiKey; //This is the api key you get from https://www.exchangerate-api.com/
        $currencyPair = $this->dto->currency_pair; //currency pair ti convert, must be like this USD_NGN
        $currencyPair = strtoupper(str_replace($currencyPair, "_", "/"));
        $url = $this->ExchangeRateApiLink."$yourExchangeRateApiKey/pair/$currencyPair";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); // Set the URL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Optional: Set a timeout
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Optional: Follow redirects
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            return json_encode(["status"=>"failed", "response"=>'cURL Error: ' . curl_error($ch)], JSON_PRETTY_PRINT);
        } else {
            curl_close($ch);
            return json_encode(["status"=>"success", "response"=>$response], JSON_PRETTY_PRINT);
        }
    }
}
?>