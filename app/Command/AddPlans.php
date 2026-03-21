<?php
namespace NewdichApp\Command;

use NewdichDto\AnsofraDto;
use NewdichSchema\Migration;
use NewdichSchema\Platform;
use NewdichSchema\Settings;

class AddPlans {

    private $dto;
    private $table = Platform::PLANS_TABLE;
    private $marchant_code = Settings::MARCHANT_CODE;
    public function __construct(AnsofraDto $dto = null) {
        $this->dto = $dto;
    }

    public function process() {

        $data =[
            "marchant_code" => $this->marchant_code
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://lan.newdich.tech/api/getplans",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($data), 
        ]);

        $response = curl_exec($ch);
        $err = curl_error($ch);

        curl_close($ch);

        if ($err) {
            return json_encode([
                "status" => "failed",
                "response" => "Error: " . $err
            ]);
        }

        $responseDec = json_decode($response, true);

        if ($responseDec["status"] !== "success") {
            return $response;
        }

        $newMigration = new Migration(null, $this->table);
        $res = $responseDec["response"];

        foreach ($res as $plan) {

            $existplan = [
                "plan" => $plan["plan"],
                "merchant_code" => $plan["merchant_code"]
            ];

            $check = $newMigration->get($existplan, 0, 1);
            $checkDec = json_decode($check, true);

            if ($checkDec["status"] === "success") {

                $updateData = [
                    "duration" => $plan["duration"],
                    "quantity" => $plan["quantity"],
                    "price" => $plan["price"],
                    "currency" => $plan["currency"],
                    "discount" => $plan["discount"]
                ];

                $newMigration->edit($updateData, $existplan);

            } else {

                $dataToSave = [
                    "plan" => $plan["plan"],
                    "duration" => $plan["duration"],
                    "quantity" => $plan["quantity"],
                    "price" => $plan["price"],
                    "currency" => $plan["currency"],
                    "discount" => $plan["discount"],
                    "merchant_code" => $plan["merchant_code"]
                ];

                $newMigration->saveUnique("plan", $plan["plan"], $dataToSave);
            }
        }

        return json_encode([
            "status" => "success",
            "response" => "Plans synced successfully"
        ]);
    }
}