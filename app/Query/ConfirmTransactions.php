<?php
namespace NewdichApp\Query;

use NewdichDto\AnsofraDto;
use NewdichSchema\Migration;
use NewdichSchema\Platform;
use NewdichSchema\Settings;

class ConfirmTransactions {

    private $dto;
    private $table = Platform::USERS_TABLE;
    private $marchant_code = Settings::MARCHANT_CODE;

    public function __construct(AnsofraDto $dto = null) {
        $this->dto = $dto;
    }

    public function process() {

        $dataToCheck = [
            "marchant_code" => $this->marchant_code
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => "https://lan.newdich.tech/api/confirmtransactions",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($dataToCheck),
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

        $res = json_decode($response, true);

        if (!$res || !isset($res["status"])) {
            return json_encode([
                "status" => "failed",
                "response" => "Invalid API response"
            ]);
        }

        if ($res["status"] !== "success") {
            return $response;
        }

        $newMigration = new Migration(null, $this->table);
        $users = $res["response"];

        foreach ($users as $user) {

            $condition = [
                "email" => $user["email"],
                "mac" => $user["mac"],
                "hashed_mac" => $user["hashed_mac"],
                "marchant_code" => $this->marchant_code
            ];

            $dataToUpdate = [
                "has_table_been_updated_locally" => "yes",
                "sub_status" => $user["sub_status"],
                "expires_at" => $user["expires_at"]
            ];

            $newMigration->edit($dataToUpdate, $condition);
        }

        return json_encode([
            "status" => "success",
            "response" => "Transactions confirmed and updated"
        ]);
    }
}
?>