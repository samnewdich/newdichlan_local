<?php
// ===== CONFIG =====
$BASE = "http://192.168.200.1:8080/newdichlan/ansofra/api";

// ===== HELPER FUNCTION =====
function postRequest($url, $data = [])
{
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log("CURL ERROR: " . curl_error($ch));
    }

    curl_close($ch);

    return json_decode($response, true);
}

// ===== GET URL PARAMS =====
$mac = strtolower(trim($_GET['mac'] ?? ''));
$ip = $_GET['ip'] ?? '';
$device = $_GET['device'] ?? '';
$current_time = $_GET['current_time'] ?? date("Y-m-d H:i:s");

// ===== LOAD PLANS =====
$plansData = postRequest("$BASE/getplans");

// ===== HANDLE FORM SUBMIT =====
$selectedPlan = null;
$accounts = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $plans_id = $_POST['plans_id'] ?? '';
    $phone = htmlspecialchars($_POST["phone"] ?? '');

    // ===== REGISTER BODY =====
    $body = [
        "mac" => $mac,
        "last_ip" => $ip,
        "device_name" => $device,
        "date_created" => $current_time,
        "current_time" => $current_time,
        "phone" => $phone
    ];

    // ===== TRY REGISTER =====
    $register = postRequest("$BASE/register", $body);

    if ($register && $register['status'] === "success") {
        $accounts = $register['response'];
    } else {
        // ===== FALLBACK TO GET RESERVED =====
        $login = postRequest("$BASE/getreserved", [
            "mac" => $mac
        ]);

        if ($login && $login['status'] === "success") {
            $accounts = $login['response'];
        } else {
            error_log("GET RESERVED FAILED: " . json_encode($login));
        }
    }

    // ===== GET SELECTED PLAN =====
    $planRes = postRequest("$BASE/geteachplans", ["plans_id" => $plans_id]);

    if ($planRes && $planRes['status'] === "success") {
        $selectedPlan = $planRes['response'][0] ?? null;
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
        <p>Enjoy Unlimited 24/7 Internet Access</p>
        <p>Choose a plan to get online</p>
    </div>
</header>

<main class="portal-main">
    <div class="container">
        <?php //echo $plansData; ?>
        <!-- PLAN SELECT -->
        <div id="plan-container">
            <?php if ($plansData && $plansData['status'] === "success"): ?>
                <form method="POST" class="form-group">
                    <label>Choose a Plan:</label>
                    <select name="plans_id">
                        <?php foreach ($plansData['response'] as $plan): ?>
                            <option value="<?= $plan['plans_id'] ?>">
                                <?= $plan['plan'] ?> - <?= $plan['currency'] ?> <?= $plan['price'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br><br>
                    <label>Phone Number:</label>
                    <input type="number" name="phone" placeholder="Phone Number" required />
                    <span style="font-size:12px;">Note: Phone number is just to create an account for you.</span>
                    <br><br>
                    <button type="submit" id="paybtn" class="btn btn-secondary" onclick="loading()">Pay</button>
                </form>
            <?php else: ?>
                <p>Failed to load plans</p>
            <?php endif; ?>
        </div>

        <!-- PAYMENT RESULT -->
        <div id="pay-container">
            <?php if ($selectedPlan && !empty($accounts)): ?>

                <script>
                    document.getElementById("plan-container").style.display="none";
                </script>
                <?php
                    $price = ($selectedPlan['discount'] && $selectedPlan['price'] >= $selectedPlan['discount'])
                        ? $selectedPlan['price'] - $selectedPlan['discount']
                        : $selectedPlan['price'];
                ?>

                <div style="margin-top:20px; padding:5px;">
                    <h3>Subscription Plan : <?= $selectedPlan['plan'] ?></h3>
                    <p><strong>Device : <?= $mac ?></strong></p>
                    <p><strong>Price : <?= $selectedPlan['currency'] ?> <?= $price ?></strong></p>
                    <p>Make payment into any account below:</p>

                    <?php foreach ($accounts as $acc): ?>
                        <div>
                            <p>Bank : <strong><?= $acc['bank']['name'] ?? $acc['bank'] ?></strong></p>
                            <p>Account Number : 
                                <span onclick="copyText('<?= $acc['account_number'] ?>')">
                                    <?= $acc['account_number'] ?>
                                </span>
                            </p>
                            <p>Account Name : <?= $acc['account_name'] ?></p>
                            <hr>
                        </div>
                    <?php endforeach; ?>
                    <p style="color:red; font-size:12px;">Note: This subscription can only be used on this device.</p>
                    <p style="color:red; font-size:12px;">Note: Do not share your hotspot with any other device, else both you and them would be blocked</p>
                </div>

            <?php endif; ?>
        </div>

    </div>
</main>

<footer>
    <div class="container">
        <p>Powered by NEWDICH TECHNOLOGY</p>
        <p>&copy; <?= date('Y') ?> Newdich Technology</p>
    </div>
</footer>
<script>
    function loading(){
        document.getElementById("paybtn").innerHTML=`
            <div>
                <img src="/newdichlan/ansofra/public/marchant/loader.gif" style="max-width:50px; max-height:50px;" />
            </div>
        `;
    }

    async function copyText(text) {
        try {
            await navigator.clipboard.writeText(text);
            alert("Copied successfully!");
        } catch (err) {
            console.error("Copy failed:", err);
        }
    }
</script>
</body>
</html>