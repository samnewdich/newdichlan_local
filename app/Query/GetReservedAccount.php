<?php
namespace NewdichApp\Query;

use NewdichDto\AnsofraDto;
use NewdichSchema\Migration;
use NewdichSchema\Platform;
use NewdichSchema\Settings;

class GetReservedAccount {

    private AnsofraDto $dto;
    private string $marchant_code;
    private string $table;

    public function __construct(AnsofraDto $dto){
        $this->dto = $dto;
        $this->marchant_code = Settings::MARCHANT_CODE;
        $this->table = Platform::RESERVED_TABLE;
    }

    public function process(): array
    {
        // ===== VALIDATE MAC =====
        $mac = $this->dto->mac ?? "";
        $macCheck = strtolower(trim($this->dto->mac ?? ""));

        if (in_array($mac, ["", "unknown", "null", "undefined"])) {
            return $this->fail("Invalid MAC address");
        }

        // ===== QUERY DB =====
        $migration = new Migration(null, $this->table);

        // IMPORTANT: ALWAYS include merchant_code internally
        $dataToCheck = [
            "mac" => $mac,
            "marchant_code" => $this->marchant_code
        ];

        $get = $migration->get($dataToCheck, 0, 50);
        $decoded = json_decode($get, true);

        // ===== VALIDATE RESPONSE =====
        if (!$this->isValidResponse($decoded)) {
            return $this->fail("Invalid response from database");
        }

        if ($decoded["status"] !== "success" || empty($decoded["response"])) {
            return $this->fail("No reserved account found");
        }

        // ===== FORMAT RESPONSE =====
        $accounts = array_map(function ($acc) {
            return [
                "account_name"   => $acc["account_name"] ?? "",
                "account_number" => $acc["account_number"] ?? "",
                "bank" => [
                    "name" => $acc["bank"] ?? ""
                ]
            ];
        }, $decoded["response"]);

        return $this->success($accounts);
    }

    // ===== HELPERS =====

    private function success($data): array
    {
        return [
            "status" => "success",
            "response" => $data
        ];
    }

    private function fail(string $message): array
    {
        return [
            "status" => "failed",
            "response" => $message
        ];
    }

    private function isValidResponse($data): bool
    {
        return is_array($data) && isset($data["status"]);
    }
}
?>








<?php
/*
namespace NewdichApp\Query;

use NewdichDto\AnsofraDto;
use NewdichSchema\Migration;
use NewdichSchema\Platform;
use NewdichSchema\Settings;

class GetReservedAccount {

    private AnsofraDto $dto;
    private string $marchant_code;
    private string $table;

    public function __construct(AnsofraDto $dto){
        $this->dto = $dto;
        $this->marchant_code = Settings::MARCHANT_CODE;
        $this->table = Platform::RESERVED_TABLE;
    }

    public function process(): array
    {
        //Validate MAC
        $mac = strtolower(trim($this->dto->mac ?? ""));

        if (in_array($mac, ["", "unknown", "null", "undefined"])) {
            return $this->fail("Invalid MAC address");
        }

        //Query DB
        $migration = new Migration(null, $this->table);

        $dataToCheck = [
            "mac" => $mac, //FIXED
            "marchant_code" => $this->marchant_code
        ];

        $get = $migration->get($dataToCheck, 0, 50);
        $decoded = json_decode($get, true);

        if (!$this->isValidResponse($decoded)) {
            return $this->fail("Invalid response from database");
        }

        if ($decoded["status"] !== "success") {
            return $decoded; // pass-through
        }

        // Format response
        $accounts = array_map(function ($acc) {
            return [
                "account_name"   => $acc["account_name"] ?? "",
                "account_number" => $acc["account_number"] ?? "",
                "bank" => [
                    "name" => $acc["bank"] ?? ""
                ]
            ];
        }, $decoded["response"]);

        return $this->success($accounts);
    }

    // ===== HELPERS =====

    private function success($data): array
    {
        return [
            "status" => "success",
            "response" => $data
        ];
    }

    private function fail(string $message): array
    {
        return [
            "status" => "failed",
            "response" => $message
        ];
    }

    private function isValidResponse($data): bool
    {
        return is_array($data) && isset($data["status"]);
    }
}
*/
?>