<?php
namespace NewdichMail;
use NewdichSchema\Settings;

class Mailgun
{
    private string $apiKey = Settings::MAILGUN_API_KEY;
    private $customEmail = Settings::MAILGUN_CUSTOMIZED_DOMAIN_EMAIL;
    private $api = Settings::MAILGUN_MAILING_ENDPOINT;
    private $appName = Settings::APP_NAME;

    public function send($recipient, $recipientName, $subject, $content)
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            \CURLOPT_URL => $this->api,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_POST => true,
            \CURLOPT_USERPWD => "api:{$this->apiKey}",
            \CURLOPT_POSTFIELDS => [
                'from' => "{$this->appName} $this->customEmail",
                'to' => $recipient,
                'subject' => $subject,
                'html' => $content
            ]
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            return json_encode(["status"=>"failed", "response"=> throw new \Exception(curl_error($ch))], JSON_PRETTY_PRINT);
        }

        $httpCode = curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            return json_encode(["status"=>"failed", "response"=> throw new \Exception("Mailgun error: " . $response)], JSON_PRETTY_PRINT);
        }

        return json_encode(["status"=>"success", "response"=>"Email delivered"], JSON_PRETTY_PRINT);
    }
}
?>