<?php
namespace NewdichApp\Query;

use NewdichSchema\Migration;
use NewdichSchema\Platform;
use NewdichDto\AnsofraDto;
use NewdichSchema\Settings;

class CheckPaid {
    private AnsofraDto $dto;
    private $table = Platform::USERS_TABLE;
    private $marchant_code = Settings::MARCHANT_CODE;

    public function __construct(AnsofraDto $dto){
        $this->dto = $dto;
    }

    public function process(): array {

        $condition = [
            "mac" => $this->dto->mac,
            "marchant_code" => $this->marchant_code
        ];

        $migration = new Migration(null, $this->table);
        $get = $migration->get($condition, 0, 1);
        $getDec = json_decode($get, true);

        if (
            isset($getDec["status"]) &&
            $getDec["status"] === "success" &&
            !empty($getDec["response"][0])
        ) {

            $user = $getDec["response"][0];
            $expiresAt = (int) $user["expires_at"];
            $currentTime = (int) $this->dto->current_time;

            // Only act if expired AND still active
            if ($expiresAt <= $currentTime && $user["sub_status"] !== "inactive") {

                // Update local DB
                $migration->edit(["sub_status" => "inactive"], $condition);

                // Send to API
                $this->notifyApi($condition);

                $user["sub_status"] = "inactive";
            }

            return $user;
        }

        return [
            "status" => "error",
            "response" => $getDec["message"] ?? "User not found or DB error",
            "sub_status" => "inactive"
        ];
    }

    private function notifyApi(array $data): void
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => "https://lan.newdich.tech/api/expireplan", //FIXED
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);

        curl_close($ch);

        // Optional logging (VERY useful in production)
        if ($error) {
            error_log("ExpirePlan API Error: " . $error);
        }
    }
}
?>




<?php
/*
namespace NewdichApp\Query;
use NewdichSchema\Migration;
use NewdichSchema\Platform;
use NewdichDto\AnsofraDto;
use NewdichSchema\Settings;

class CheckPaid{
    private AnsofraDto $dto;
    private $table = Platform::USERS_TABLE;
    private $marchant_code = Settings::MARCHANT_CODE;

    public function __construct(AnsofraDto $dto){
        $this->dto = $dto;
    }

    public function process(): array {
        $condition = [
            "mac" => $this->dto->mac,
            "marchant_code" => $this->marchant_code
        ];

        $newMigration = new Migration(null, $this->table);
        $get = $newMigration->get($condition, 0, 1);
        $getDec = json_decode($get, true);

        // Successful fetch
        if(isset($getDec["status"]) && $getDec["status"] === "success" && !empty($getDec["response"][0])){
            $response = $getDec["response"][0];
            $expiresAt = (int) $response["expires_at"];

            if($expiresAt <= (int) $this->dto->current_time){
                // Expired → mark inactive
                $newMigration->edit(["sub_status" => "inactive"], $condition);

                //ALSO SEND TO API
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => "https://lan.newdich.tech/api/getplans",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => [
                        "Content-Type: application/json"
                    ],
                    CURLOPT_POSTFIELDS => json_encode($condition),
                ]);

                $responseApi = curl_exec($ch);
                $err = curl_error($ch);
                curl_close($ch);
                //No need to listen to the response

                $response["sub_status"] = "inactive";
            }

            return $response; // array returned
        }

        // Fallback → API failure or user not found
        return [
            "status" => "error",
            "response" => $getDec["message"] ?? "User not found or DB error",
            "sub_status" => "inactive"
        ];
    }
}
*/
?>