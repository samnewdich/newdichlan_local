<?php
namespace NewdichMail;
use NewdichSchema\Settings;

class Sendgrid{
    private $sendgridApiKey = Settings::SENDGRID_API_KEY;
    private $customEmail = Settings::SENDGRID_CUSTOMIZED_DOMAIN_EMAIL;
    private $api = Settings::SENDGRID_MAILING_ENDPOINT;
    
    public function send($recipient, $recipientName, $subject, $content){
        $data = [
            "personalizations"=>[
                [
                    "to"=>[
                        ["email"=>$recipient]
                    ],
                    "subject"=>$subject
                ]
            ],
            "from"=>[
                "email"=>$recipient,
                "name"=>$recipientName
            ],
            "content"=>[
                [
                    "type"=>"text/html",
                    "value"=>$content
                ]
            ]
        ];

        $ch = curl_init($this->api);
        curl_setopt_array($ch, [
            \CURLOPT_POST=>true,
            \CURLOPT_HTTPHEADER=>[
                "Authorization: Bearer $this->sendgridApiKey",
                "Content-Type: application/json"
            ],
            \CURLOPT_RETURNTRANSFER=>true,
            \CURLOPT_POSTFIELDS=>json_encode($data)
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($httpCode >= 200 && $httpCode < 300){
            return json_encode(["status"=>"success", "response"=>"Email sent successfully"], JSON_PRETTY_PRINT);
        }
        else{
            return json_encode(["status"=>"failed", "response"=>"Failed to send email ".$response], JSON_PRETTY_PRINT);
        }
    }
}
?>