<?php
namespace NewdichControllerApp;
use NewdichApis\PaystackWebhook;
use NewdichSchema\Settings;

$paystackSecret = Settings::PAYSTACK_SECRET_KEY;

// Read raw POST data
$input = file_get_contents('php://input');
$event = json_decode($input, true);

// Basic security: Verify Paystack signature (recommended in production)
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
$computed = hash_hmac('sha512', $input, $paystackSecret);

if ($signature !== $computed) {
  echo json_encode([
    "status"=>"failed",
    "response"=>"Invalid credentials"
  ], JSON_PRETTY_PRINT);
  http_response_code(401);
  exit;
}

$newPaystack = new PaystackWebhook($event);
echo $newPaystack->process();
exit;
?>