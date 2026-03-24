<?php

require_once __DIR__ . '/vendor/autoload.php';

use NewdichApp\Command\AddPlans;

echo "Plan sync worker started...\n";

while (true) {

    $addPlans = new AddPlans();
    $result = $addPlans->process();

    echo $result . "\n";

    sleep(20);
}

?>