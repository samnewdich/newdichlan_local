<?php
/**
 * Auto-Migration Script for Ansofra Framework
 * This assumnes you have set SERVER, SERVER_USER, SERVER_DB,SERVER_PASS in the .env file
 * Automatically creates tables defined in NewdichSchema\Platform.
 * 
 * IMPORTANT:
 * - Only set $createDatabase to true when you want to create database
 * - Change $createDatabase to false after first successful run
 * - In production: protect or remove this file after initial setup
 *
 * USAGE
 *  - In Development:Run this in broswer: http://localhost/ansofra/apiadmin/run_migration
 *  - In Production:Run this in browser: http://yourdomain.com/apiadmin/run_migration
 */

namespace NewdichSchema;

require_once __DIR__ . '/Settings.php';
require_once __DIR__ . '/Platform.php';
require_once __DIR__ . '/Migration.php';

use NewdichSchema\Migration;
use NewdichSchema\Platform;
use NewdichSchema\Settings;
use ReflectionClass;

$createDatabase = false;           // Set to false after first run!
$verbose        = true;            // show more details
$dbName = Settings::SERVER_DB;
if (empty($dbName)) {
    die("ERROR: SERVER_DB is not set in .env / Settings.php\n");
}

if ($createDatabase) {
    echo "Attempting to create database: $dbName ...<br>";
    $dbMigration = new Migration();
    $dbResult = $dbMigration->createDB($dbName);
    echo $dbResult . "<br>";
} else {
    echo "Database creation skipped (already done or disabled).<hr>";
}


$reflection = new ReflectionClass(Platform::class);
$constants  = $reflection->getConstants();
$tablesToMigrate = [];

foreach ($constants as $constName => $value) {
    if (!str_ends_with($constName, '_TABLE_COLUMNS')) {
        continue;
    }

    // Get corresponding table name constant
    $tableConstName = str_replace('_TABLE_COLUMNS', '_TABLE', $constName);

    if (!defined("NewdichSchema\\Platform::$tableConstName")) {
        if ($verbose) {
            echo "[SKIP] Missing matching table constant: $tableConstName <br>";
        }
        continue;
    }

    $tableName = constant("NewdichSchema\\Platform::$tableConstName");

    // Safety checks
    if (!is_string($tableName) || trim($tableName) === '') {
        if ($verbose) {
            echo "[SKIP] Invalid/empty table name for $constName → '$tableName' <br>";
        }
        continue;
    }

    if (!is_array($value) || empty($value)) {
        if ($verbose) {
            echo "[SKIP] Empty or invalid columns for table '$tableName' <br>";
        }
        continue;
    }

    // Store the pair
    $tablesToMigrate[] = [
        'table'   => $tableName,
        'columns' => $value,
        'const'   => $constName,
    ];
}


if (empty($tablesToMigrate)) {
    die("No valid tables found to migrate. <br>");
}

echo "Found " . count($tablesToMigrate) . " tables to create/migrate.<br>";
echo str_repeat("=", 60) . "<br><br>";

foreach ($tablesToMigrate as $item) {
    $tableName = $item['table'];
    $columns   = $item['columns'];
    $constName = $item['const'];

    echo "Processing table: $tableName <br>";
    echo "  → From constant: $constName <br>";
    echo "  → Columns: " . count($columns) . "<br>";

    try {
        $migration = new Migration($columns, $tableName);
        $result = $migration->createTB();
        echo $result;
    } catch (\Throwable $e) {
        echo "→ EXCEPTION: " . $e->getMessage() . "<hr/>";
    }

    echo str_repeat("-", 60) . "<hr/>";
}

echo "Migration finished.\n";

/**OR If you prefer manual migration */
/*
$dbName = Settings::SERVER_DB;
$usersTable = Platform::USERS;
$usersTableColumns = Platform::USERS_COLUMNS;
$adminTable = Platform::ADMINS;
$adminTableColumns = Platform::ADMINS_COLUMNS;

//NOTE: YOU CAN COMMENT OR REMOVE ANY MIGRATION YOU DON'T NEED
//YOU CAN ADD ANY MIGRATION YOU WANT.

//Create DB(NB:comment it out if you already created DB)
$newMigration = new Migration();
echo $newMigration->createDB($dbName);
//exit;

//create table(NB:comment it out if you have alredy created the table)
$newMigration = new Migration($adminTableColumns, $adminTable); //pass the table columns and the table name as contructor. check the Platform.php file to see how the table columns must be
echo $newMigration->createTB();
exit;
*/
?>