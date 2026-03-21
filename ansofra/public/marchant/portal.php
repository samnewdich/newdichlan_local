<?php
// ===== CONFIG =====
$BASE = "http://192.168.200.1:8080/newdichlan/ansofra/api";

// ===== HELPER FUNCTION =====
function postRequest($url, $data = [])
{
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// ===== GET URL PARAMS =====
$mac = $_GET['mac'] ?? '';
$ip = $_GET['ip'] ?? '';
$device = $_GET['device'] ?? '';
$current_time = $_GET['current_time'] ?? '';

// ===== LOAD PLANS =====
$plansData = postRequest("$BASE/getplans");

// ===== HANDLE FORM SUBMIT =====
$selectedPlan = null;
$accounts = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $plans_id = $_POST['plans_id'];

    $body = [
        "mac" => $mac,
        "last_ip" => $ip,
        "device_name" => $device,
        "date_created" => $current_time,
        "current_time" => $current_time,
        "phone" => ""
    ];

    // Try register
    $register = postRequest("$BASE/register", $body);

    if ($register['status'] === "success") {
        $accounts = $register['response'];
    } else {
        // fallback
        $login = postRequest("$BASE/getreserved", ["mac" => $mac]);
        if ($login['status'] === "success") {
            $accounts = $login['response'];
        }
    }

    // Get selected plan
    $planRes = postRequest("$BASE/geteachplans", ["plans_id" => $plans_id]);

    if ($planRes['status'] === "success") {
        $selectedPlan = $planRes['response'][0];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newdich LAN</title>
    <link rel="stylesheet" href="/newdichlan/ansofra/public/marchant/css/style.css">
</head>
<body>

<header class="portal-header">
    <div class="container">
        <h1>Welcome to <span>Newdich LAN</span></h1>
        <p>Choose a plan to get online</p>
    </div>
</header>

<main class="portal-main">
    <div class="container">
        <?php echo $plansData; ?>
        <!-- PLAN SELECT -->
        <?php if ($plansData && $plansData['status'] === "success"): ?>
            <form method="POST">
                <select name="plans_id">
                    <?php foreach ($plansData['response'] as $plan): ?>
                        <option value="<?= $plan['plans_id'] ?>">
                            <?= $plan['plan'] ?> - <?= $plan['currency'] ?> <?= $plan['price'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br><br>
                <button type="submit">Pay</button>
            </form>
        <?php else: ?>
            <p>Failed to load plans</p>
        <?php endif; ?>

        <!-- PAYMENT RESULT -->
        <?php if ($selectedPlan && !empty($accounts)): ?>

            <?php
                $price = ($selectedPlan['discount'] && $selectedPlan['price'] >= $selectedPlan['discount'])
                    ? $selectedPlan['price'] - $selectedPlan['discount']
                    : $selectedPlan['price'];
            ?>

            <div style="margin-top:20px;">
                <h3><?= $selectedPlan['plan'] ?></h3>
                <p><strong><?= $selectedPlan['currency'] ?> <?= $price ?></strong></p>
                <p>Make payment into any account below:</p>

                <?php foreach ($accounts as $acc): ?>
                    <div>
                        <p><strong><?= $acc['bank']['name'] ?></strong></p>
                        <p><?= $acc['account_number'] ?></p>
                        <p><?= $acc['account_name'] ?></p>
                        <hr>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>
</main>

<footer>
    <div class="container">
        <p>Powered by NEWDICH TECHNOLOGY</p>
        <p>&copy; <?= date('Y') ?> Newdich Technology</p>
    </div>
</footer>

</body>
</html>