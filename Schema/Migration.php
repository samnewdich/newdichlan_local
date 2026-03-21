<?php
namespace NewdichSchema;
// MANUAL INCLUDES (REQUIRED)
require_once __DIR__ . '/Settings.php';
require_once __DIR__ . '/Platform.php';

use NewdichSchema\Settings;
use NewdichSchema\Platform;
use PDO;
use PDOException;

class Migration{
    private $table;
    private $columns = [];
    private $rows = [];
    private $conn;
    private $conndb;


    public function __construct(?array $columns = null, ?string $table = null)
    {
        // Assign only if provided
        if ($table !== null) {
            $this->table = $table;
        }

        if ($columns !== null) {
            $this->columns = $columns;
        }
        
        $rootDir = Settings::ROOT_DIRECTORY; 

        require __DIR__ . "/Dealer.php";
        $this->conn = $connnewdich;
        $this->conndb = $connnewdichdb;
    }


    public function createDB(string $dbname)
    {
        $dbname = preg_replace('/[^a-zA-Z0-9_]/', '', $dbname);

        $sql = "CREATE DATABASE IF NOT EXISTS `$dbname`
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

        $this->conndb->exec($sql);
        echo "Database $dbname successfully created";
        //exit;
    }



    public function createTB()
    {
        $columns = array_filter($this->columns);

        if (empty($columns)) {
            throw new Exception("No columns supplied");
        }

        $columnsSQL = implode(",\n", $columns);

        $sql = "
            CREATE TABLE IF NOT EXISTS `{$this->table}` (
                $columnsSQL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";

        $this->conn->exec($sql);
        echo "Table " .$this->table. " was created successfully";
        //exit;
    }



    public function getTableColumns(PDO $pdo, string $table): array
    {
        //sanitize table name
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);

        $stmt = $pdo->query("DESCRIBE `$table`");

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }



    public function saveUnique(string $uniqueCol, $uniqueValue, array $rowsInKeyValue)
    {
        try {
            if (empty($rowsInKeyValue)) {
                return json_encode([
                    "status" => "failed",
                    "response" => "No data supplied"
                ], JSON_PRETTY_PRINT);
            }

            //get table columns
            $columns = $this->getTableColumns($this->conn, $this->table);
            //print_r($columns);

            //VALIDATE UNIQUE COLUMN
            if (!in_array($uniqueCol, $columns, true)) {
                return json_encode([
                    "status" => "failed",
                    "response" => "Invalid unique column"
                ], JSON_PRETTY_PRINT);
            }

            //FILTER ONLY ALLOWED COLUMNS
            $data = [];
            foreach ($rowsInKeyValue as $col => $val) {
                if (in_array($col, $columns, true)) {
                    $data[$col] = $val;
                }
            }

            if (empty($data)) {
                return json_encode([
                    "status" => "failed",
                    "response" => "No valid columns supplied"
                ], JSON_PRETTY_PRINT);
            }

            $table = $this->table;

            //DUPLICATE CHECK
            $check = $this->conn->prepare(
                "SELECT 1 FROM `$table` WHERE `$uniqueCol` = :val LIMIT 1"
            );
            $check->bindValue(':val', $uniqueValue);
            $check->execute();

            if ($check->fetchColumn()) {
                return json_encode([
                    "status" => "failed",
                    "response" => "duplicate entry"
                ], JSON_PRETTY_PRINT);
            }

            //BUILD INSERT
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ":$c", $cols);

            $sql = "INSERT INTO `$table` (`" . implode('`,`', $cols) . "`)
                    VALUES (" . implode(',', $placeholders) . ")";

            $stmt = $this->conn->prepare($sql);

            foreach ($data as $col => $val) {
                $stmt->bindValue(":$col", $val);
            }

            $stmt->execute();

            return json_encode([
                "status" => "success",
                "response" => "saved successfully",
                "id" => $this->conn->lastInsertId()
            ], JSON_PRETTY_PRINT);

        } catch (PDOException $e) {
            return json_encode([
                "status" => "failed",
                "response" => $e->getMessage()
            ], JSON_PRETTY_PRINT);
        }
    }




    public function saveUniqueMulti(array $uniqueCol, array $uniqueValue, array $rowsInKeyValue)
    {
        try {
            if (empty($rowsInKeyValue)) {
                return json_encode([
                    "status" => "failed",
                    "response" => "No data supplied"
                ], JSON_PRETTY_PRINT);
            }

            // Columns in table
            $columns = $this->getTableColumns($this->conn, $this->table);

            // Validate unique columns
            foreach ($uniqueCol as $col) {
                if (!in_array($col, $columns, true)) {
                    return json_encode([
                        "status" => "failed",
                        "response" => "Invalid unique column: $col"
                    ], JSON_PRETTY_PRINT);
                }
            }

            // Validate unique values count
            if (count($uniqueCol) !== count($uniqueValue)) {
                return json_encode([
                    "status" => "failed",
                    "response" => "Unique columns and values count mismatch"
                ], JSON_PRETTY_PRINT);
            }

            // Filter only allowed columns
            $data = [];
            foreach ($rowsInKeyValue as $col => $val) {
                if (in_array($col, $columns, true)) {
                    $data[$col] = $val;
                }
            }

            if (empty($data)) {
                return json_encode([
                    "status" => "failed",
                    "response" => "No valid columns supplied"
                ], JSON_PRETTY_PRINT);
            }

            $table = $this->table;

            // BUILD DUPLICATE CHECK
            $where = [];
            foreach ($uniqueCol as $col) {
                $where[] = "`$col` = :$col";
            }

            $sqlCheck = "SELECT 1 FROM `$table` WHERE " . implode(' AND ', $where) . " LIMIT 1";
            $check = $this->conn->prepare($sqlCheck);

            foreach ($uniqueCol as $i => $col) {
                $check->bindValue(":$col", $uniqueValue[$i]);
            }

            $check->execute();

            if ($check->fetchColumn()) {
                return json_encode([
                    "status" => "failed",
                    "response" => "duplicate entry"
                ], JSON_PRETTY_PRINT);
            }

            // BUILD INSERT
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ":$c", $cols);

            $sqlInsert = "INSERT INTO `$table` (`" . implode('`,`', $cols) . "`)
                        VALUES (" . implode(',', $placeholders) . ")";

            $stmt = $this->conn->prepare($sqlInsert);

            foreach ($data as $col => $val) {
                $stmt->bindValue(":$col", $val);
            }

            $stmt->execute();

            return json_encode([
                "status" => "success",
                "response" => "saved successfully",
                "id" => $this->conn->lastInsertId()
            ], JSON_PRETTY_PRINT);

        } catch (PDOException $e) {
            return json_encode([
                "status" => "failed",
                "response" => $e->getMessage()
            ], JSON_PRETTY_PRINT);
        }
    }






    public function save(array $rowsInKeyValue)
    {
        try {

            if (empty($rowsInKeyValue)) {
                return json_encode([
                    "status" => "failed",
                    "response" => "No data supplied"
                ], JSON_PRETTY_PRINT);
            }

            //get table columns
            $columns = $this->getTableColumns($this->conn, $this->table);

            //FILTER ONLY ALLOWED COLUMNS
            $data = [];
            foreach ($rowsInKeyValue as $col => $val) {
                if (in_array($col, $columns, true)) {
                    $data[$col] = $val;
                }
            }

            if (empty($data)) {
                return json_encode([
                    "status" => "failed",
                    "response" => "No valid columns supplied"
                ], JSON_PRETTY_PRINT);
            }


            $table = $this->table;

            //BUILD INSERT
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ":$c", $cols);

            $sql = "INSERT INTO `$table` (`" . implode('`,`', $cols) . "`)
                    VALUES (" . implode(',', $placeholders) . ")";

            $stmt = $this->conn->prepare($sql);

            foreach ($data as $col => $val) {
                $stmt->bindValue(":$col", $val);
            }

            $stmt->execute();

            return json_encode([
                "status" => "success",
                "response" => "saved successfully"
            ], JSON_PRETTY_PRINT);

        } catch (PDOException $e) {
            return json_encode([
                "status" => "failed",
                "response" => $e->getMessage()
            ], JSON_PRETTY_PRINT);
        }
    }




    public function get(array $where = [], int $offset = 0, int $limit = 20)
    {
        try {
            $table = $this->table;
            $sql   = "SELECT * FROM `$table`";
            $binds = [];

            if (!empty($where)) {
                $conditions = [];
                foreach ($where as $col => $val) {
                    $conditions[] = "`$col` = :$col";
                    $binds[":$col"] = $val;
                }
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            // Ensure integers for LIMIT
            $offset = (int) $offset;
            $limit  = (int) $limit;

            $sql .= " ORDER BY `{$table}_id` DESC LIMIT $offset, $limit";

            $stmt = $this->conn->prepare($sql);

            foreach ($binds as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                return json_encode([
                    "status"   => "success",
                    "response" => $rows
                ], JSON_PRETTY_PRINT);
            }

            return json_encode([
                "status"   => "failed",
                "response" => "No record found"
            ], JSON_PRETTY_PRINT);

        } catch (PDOException $e) {
            return json_encode([
                "status"   => "failed",
                "response" => $e->getMessage()
            ], JSON_PRETTY_PRINT);
        }
    }




    public function getSpecific(array $where = [], array $columnsToSelect = [], int $offset = 0, int $limit = 20)
    {
        try {
            $table = $this->table;

            // Get allowed columns from DB
            $allowedColumns = $this->getTableColumns($this->conn, $table);

            /* ---------------------------
            * SELECT COLUMNS
            * --------------------------- */
            if (empty($columnsToSelect)) {
                $select = "*";
            } else {
                // Filter only valid columns
                $validColumns = array_intersect($columnsToSelect, $allowedColumns);

                if (empty($validColumns)) {
                    return json_encode([
                        "status" => "failed",
                        "response" => "Invalid columns selected"
                    ], JSON_PRETTY_PRINT);
                }

                $select = "`" . implode("`,`", $validColumns) . "`";
            }

            $sql   = "SELECT $select FROM `$table`";
            $binds = [];

            /* ---------------------------
            * WHERE CONDITIONS
            * --------------------------- */
            if (!empty($where)) {
                $conditions = [];

                foreach ($where as $col => $val) {
                    if (!in_array($col, $allowedColumns, true)) {
                        continue; // skip invalid column
                    }

                    $param = ":w_$col";
                    $conditions[] = "`$col` = $param";
                    $binds[$param] = $val;
                }

                if (!empty($conditions)) {
                    $sql .= " WHERE " . implode(" AND ", $conditions);
                }
            }

            /* ---------------------------
            * PAGINATION
            * --------------------------- */
            $offset = max(0, (int) $offset);
            $limit  = max(1, (int) $limit);

            // Prefer explicit primary key if possible
            $sql .= " ORDER BY `id` DESC LIMIT $offset, $limit";

            $stmt = $this->conn->prepare($sql);

            foreach ($binds as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode([
                "status" => "success",
                "count" => count($rows),
                "response" => $rows
            ], JSON_PRETTY_PRINT);

        } catch (PDOException $e) {
            return json_encode([
                "status" => "failed",
                "response" => $e->getMessage()
            ], JSON_PRETTY_PRINT);
        }
    }




    public function count(array $where = []): string
    {
        try {
            $table = $this->table;

            $sql   = "SELECT COUNT(`{$table}_id`) AS total FROM `$table`";
            $binds = [];

            if (!empty($where)) {
                $conditions = [];
                foreach ($where as $col => $val) {
                    $conditions[]     = "`$col` = :$col";
                    $binds[":$col"]   = $val;
                }
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            $stmt = $this->conn->prepare($sql);

            foreach ($binds as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();

            $total = (int) $stmt->fetchColumn();

            return json_encode([
                "status"   => "success",
                "response" => $total
            ], JSON_PRETTY_PRINT);

        } catch (PDOException $e) {
            return json_encode([
                "status"   => "failed",
                "response" => $e->getMessage()
            ], JSON_PRETTY_PRINT);
        }
    }








    public function remove(array $where)
    {
        try {
            $table = $this->table;
            $conditions = [];
            foreach ($where as $col => $val) {
                $conditions[] = "`$col` = :$col";
            }

            $sql = "DELETE FROM `$table` WHERE " . implode(" AND ", $conditions) . " LIMIT 1";
            $stmt = $this->conn->prepare($sql);

            foreach ($where as $col => $val) {
                $stmt->bindValue(":$col", $val);
            }

            $stmt->execute();
            return json_encode(["status" => "success", "response" =>"successfully deleted row ".$stmt->rowCount()], JSON_PRETTY_PRINT);

        } catch (PDOException $e) {
            return json_encode(["status"=>"failed", "response" => $e->getMessage()], JSON_PRETTY_PRINT);
        }
    }




    public function edit(array $data, array $where)
    {
        try {
            $table = $this->table;

            $set = [];
            foreach ($data as $col => $val) {
                $set[] = "`$col` = :set_$col";
            }

            $conditions = [];
            foreach ($where as $col => $val) {
                $conditions[] = "`$col` = :where_$col";
            }

            $sql = "UPDATE `$table` SET " . implode(',', $set)
                . " WHERE " . implode(' AND ', $conditions)
                . " LIMIT 1";

            $stmt = $this->conn->prepare($sql);

            foreach ($data as $col => $val) {
                $stmt->bindValue(":set_$col", $val);
            }
            foreach ($where as $col => $val) {
                $stmt->bindValue(":where_$col", $val);
            }

            $stmt->execute();
            return json_encode(["status" => "success", "response" => $stmt->rowCount()], JSON_PRETTY_PRINT);

        } catch (PDOException $e) {
            return json_encode(["status"=>"failed", "response" => $e->getMessage()], JSON_PRETTY_PRINT);
        }
    }



    public function removeTable(): bool
    {
        try {
            $table = preg_replace('/[^a-zA-Z0-9_]/', '', $this->table);
            $this->conn->exec("DROP TABLE IF EXISTS `$table`");
            return true;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }





    
}
?>