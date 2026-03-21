<?php

require_once __DIR__ . '/vendor/autoload.php';

use NewdichApp\Command\AddPlans;
use NewdichApp\Query\ConfirmTransactions;

echo "Plan sync worker started...\n";

while (true) {

    $addPlans = new AddPlans();
    $result = $addPlans->process();

    $confirmTransactions = new ConfirmTransactions();
    $confirmTransactions->process();

    echo $result . "\n";

    sleep(10);
}

?>