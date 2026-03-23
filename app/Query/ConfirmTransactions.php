<?php
namespace NewdichApp\Query;

use NewdichDto\AnsofraDto;
use NewdichSchema\Migration;
use NewdichSchema\Platform;
use NewdichSchema\Settings;

class ConfirmTransactions {

    private $dto;
    private $table;
    private $marchant_code;

    public function __construct(AnsofraDto $dto = null) {
        $this->dto = $dto;
        $this->table = Platform::USERS_TABLE;
        $this->marchant_code = Settings::MARCHANT_CODE;
    }

    public function process(): array {

        $payload = [
            "marchant_code" => $this->marchant_code
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => "https://lan.newdich.tech/api/confirmtransactions",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($err) {
            return $this->fail("cURL Error: " . $err);
        }

        if ($httpCode !== 200) {
            return $this->fail("HTTP Error: " . $httpCode);
        }

        $res = json_decode($response, true);

        if (!$this->isValid($res)) {
            return $this->fail("Invalid API response");
        }

        if ($res["status"] !== "success") {
            return $res;
        }

        $users = $res["response"]["users"] ?? [];

        if (empty($users)) {
            return $this->success("No transactions to update");
        }

        $migration = new Migration(null, $this->table);

        $updated = 0;

        foreach ($users as $user) {

            if (empty($user["hashed_mac"])) {
                continue;
            }

            $condition = [
                "hashed_mac" => $user["hashed_mac"],
                "marchant_code" => $this->marchant_code
            ];

            $dataToUpdate = [
                "has_table_been_updated_locally" => "yes",
                "sub_status" => $user["sub_status"] ?? "",
                "expires_at" => $user["expires_at"] ?? ""
            ];

            $edit = $migration->edit($dataToUpdate, $condition);
            $editDec = json_decode($edit, true);

            if ($this->isValid($editDec) && $editDec["status"] === "success") {
                $updated++;
            }
        }

        return $this->success([
            "total_received" => count($users),
            "updated_successfully" => $updated
        ]);
    }

    private function success($data): array {
        return [
            "status" => "success",
            "response" => $data
        ];
    }

    private function fail(string $msg): array {
        return [
            "status" => "failed",
            "response" => $msg
        ];
    }

    private function isValid($data): bool {
        return is_array($data) && isset($data["status"]);
    }
}
?>