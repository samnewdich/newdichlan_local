<?php
namespace NewdichAnsofra\public;
use NewdichSchema\Settings;
use NewdichSchema\Platform;
use NewdichSchema\Migration;

$paystackPublicKey = Settings::PAYSTACK_PUBLIC_KEY;
$table = Platform::PAID_DEVICES_TABLE;
// Optional: Get MAC from query string (sent by Kali redirect)
$mac = $_GET['mac'] ?? '';
$ip  = $_GET['ip']  ?? '';
$current_time = $_GET["current_time"] ?? '';

echo $mac;
echo '<br/>';
echo $ip;
//exit;

// If already paid → auto-redirect to success (optional)
$nownow = (int) $current_time;
$dataToGet = [
  "mac" => $mac
];
$newMigration = new Migration(null, $table);
$get = $newMigration->get($dataToGet, 0, 1);
$getDec = json_decode($get, true);
if($getDec["status"] ==="success"){
  $response = $getDec["response"][0];
  $expiresAt = $response["expires_at"];
  $expiresAt = (int) $expiresAt;

  //check if it has expired
  if($expiresAt > $nownow){
    //he still has data or access
    $dataupdate = ["active"=>1];
    $edit = $newMigration->edit($dataupdate, $dataToGet);
    return;
  }
}

/*
$pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare("SELECT * FROM paid_devices WHERE mac = ? AND active = 1 AND expires_at > NOW()");
$stmt->execute([$mac]);
if ($stmt->fetch()) {
    header("Location: success.php?mac=" . urlencode($mac));
    exit;
}
*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Subscribe to Internet Access</title>
  <script src="https://js.paystack.co/v1/inline.js"></script>
  <style>
    body { font-family: Arial, sans-serif; text-align: center; padding: 5px; background: #f8f9fa; }
    .container { max-width: 500px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
    select{ width: 100%; padding: 12px; margin: 10px 0; font-size: 16px; }
    input { width: 90%; padding: 12px; margin: 10px 0; font-size: 16px; }
    button { background: #28a745; color: white; border: none; padding: 15px; font-size: 18px; cursor: pointer; width: 100%; border-radius: 5px; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Newdich LAN</h1>
    <h3>Internet Access Subscription</h3>
    <p>Please subscribe to continue browsing.</p>

    <form id="paymentForm">
      <input type="hidden" name="mac" value="<?= htmlspecialchars($mac) ?>">
      <input type="hidden" name="ip" value="<?= htmlspecialchars($ip) ?>">

      <input type="text" id="email" placeholder="Your email address or phone" required>

      <select id="plan" required>
        <option value="">Select Plan</option>
        <option value="1hour">1 Hour - ₦20</option>
        <option value="6hours">6 Hours - ₦60</option>
        <option value="12hours">12 Hours - ₦100</option>
        <option value="1day">1 Day(24 hours) – ₦200</option>
        <option value="2days">2 Days(48 hours) - ₦350</option>
        <option value="3days">3 Days(72 hours) - ₦550</option>
        <option value="4days">4 Days(96 hours) - ₦750</option>
        <option value="5days">5 Days(120 hours) - ₦970</option>
        <option value="6days">6 Days(144 hours) - ₦1,170</option>
        <option value="1week">1 Week(7 days) - ₦1,370</option>
        <option value="2weeks">2 Weeks(14 days) - ₦2,730</option>
        <option value="3weeks">3 Weeks(21 days) - ₦4,100</option>
        <option value="1month">1 Month(4 weeks) - ₦5,450</option>
      </select>

      <button type="submit">Pay Now</button>
    </form>

    <div style="margin-top:50px; padding:10px; text-align:center;">
      <a hreft="https://newdich.tech" target="_blank" style="color:black; font-weight:bolder; cursor:pointer;">&copy; Developed By Newdich Technology - <?php echo date("Y"); ?></a>
    </div>
  </div>

  <script>
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const email = document.getElementById('email').value;
      const plan = document.getElementById('plan').value;
      const mac = '<?= addslashes($mac) ?>';
      const ip = '<?= addslashes($ip) ?>';
      const current_time = Math.floor(Date.now() / 1000);

      let amount;
      if (plan === '1hour') amount = 20 * 100;
      else if (plan === '6hours') amount = 60 * 100;
      else if (plan === '12hours') amount = 100 * 100;
      else if (plan === '1day') amount = 200 * 100;
      else if (plan === '2days') amount = 350 * 100;
      else if (plan === '3days') amount = 550 * 100;
      else if (plan === '4days') amount = 750 * 100;
      else if (plan === '5days') amount = 970 * 100;
      else if (plan === '6days') amount = 1170 * 100;
      else if (plan === '1week') amount = 1370 * 100;
      else if (plan === '2weeks') amount = 2730 * 100;
      else if (plan === '3weeks') amount = 4100 * 100;
      else if (plan === '1month') amount = 5450 * 100;
      else { alert('Select a plan'); return; }

      const handler = PaystackPop.setup({
        key: '<?php echo $paystackPublicKey; ?>',
        email: email,
        amount: amount,
        metadata: { mac, ip, plan, current_time },
        callback: function(response) {
          // Payment successful - redirect to success page
          window.location.href = 'success.php?reference=' + response.reference + '&mac=' + encodeURIComponent(mac);
        },
        onClose: function() {
          alert('Payment window closed.');
        }
      });
      handler.openIframe();
    });
  </script>
</body>
</html>