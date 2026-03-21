<?php
namespace NewdichMail;
use NewdichSchema\Settings;

class Mailersend
{
    private string $apiKey = Settings::MAILERSEND_API_KEY;
    private string $endpoint = Settings::MAILERSEND_MAILING_ENDPOINT;
    private string $mailgunmail = Settings::MAILERSEND_CUSTOMIZED_DOMAIN_EMAIL;
    private string $appname = Settings::APP_NAME;

    public function send($recipient, $recipientName, $subject, $content){
        $payload = [
            "from" => [
                "email" => $this->mailgunmail,
                "name"  => $this->appname
            ],
            "to" => [
                [
                    "email" => $recipient,
                    "name"  => $recipientName
                ]
            ],
            "subject" => $subject,
            "html" => $content
        ];

        $ch = curl_init($this->endpoint);

        curl_setopt_array($ch, [
            \CURLOPT_POST           => true,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$this->apiKey}",
                "Content-Type: application/json"
            ],
            \CURLOPT_POSTFIELDS     => json_encode($payload),
            \CURLOPT_TIMEOUT        => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            curl_close($ch);
            return json_encode([
                "status" => "error",
                "response" => curl_error($ch)
            ], JSON_PRETTY_PRINT);
        }

        curl_close($ch);

        return json_encode([
            "status" => $httpCode >= 200 && $httpCode < 300 ? "success" : "failed",
            "http_code" => $httpCode,
            "response" => json_decode($response, true)
        ], JSON_PRETTY_PRINT);
    }
}
?>