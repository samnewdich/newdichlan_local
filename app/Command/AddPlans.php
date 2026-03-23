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

        $payload = [
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
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return json_encode([
                "status" => "failed",
                "response" => "cURL Error: " . $err
            ]);
        }

        $responseDec = json_decode($response, true);

        if (!isset($responseDec["status"]) || $responseDec["status"] !== "success") {
            return json_encode([
                "status" => "failed",
                "response" => "Invalid API response",
                "raw" => $response
            ]);
        }

        $plansFromApi = $responseDec["response"] ?? [];

        $migration = new Migration(null, $this->table);

        // Fetch all existing plans ONCE
        $existingRaw = $migration->get(
            ["marchant_code" => $this->marchant_code],
            0,
            1000
        );

        $existingDec = json_decode($existingRaw, true);
        $existingPlans = $existingDec["response"] ?? [];

        // Build lookup map
        $planMap = [];
        foreach ($existingPlans as $p) {
            $key = $p["plan"] . "_" . $p["marchant_code"];
            $planMap[$key] = $p;
        }

        foreach ($plansFromApi as $plan) {

            $key = $plan["plan"] . "_" . $plan["marchant_code"];

            $updateData = [
                "duration" => $plan["duration"],
                "quantity" => $plan["quantity"],
                "price" => $plan["price"],
                "currency" => $plan["currency"],
                "discount" => $plan["discount"]
            ];

            if (isset($planMap[$key])) {
                // UPDATE
                $migration->edit($updateData, [
                    "plan" => $plan["plan"],
                    "marchant_code" => $plan["marchant_code"]
                ]);
            } else {
                // INSERT
                $migration->save([
                    "plan" => $plan["plan"],
                    "duration" => $plan["duration"],
                    "quantity" => $plan["quantity"],
                    "price" => $plan["price"],
                    "currency" => $plan["currency"],
                    "discount" => $plan["discount"],
                    "marchant_code" => $plan["marchant_code"]
                ]);
            }
        }

        return json_encode([
            "status" => "success",
            "response" => "Plans synced successfully"
        ]);
    }
}
?>



<?php
/*
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
                "marchant_code" => $plan["marchant_code"]
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
                    "marchant_code" => $plan["marchant_code"]
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
*/
?>