<?php

require_once __DIR__ . '/vendor/autoload.php';

use NewdichApp\Query\ConfirmTransactions;

echo "Confirm Transactions sync worker started...\n";

while (true) {
    $confirmTransactions = new ConfirmTransactions();
    $confirmTransactions->process();

    echo $result . "\n";

    sleep(10);
}

?>