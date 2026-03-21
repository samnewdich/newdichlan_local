<?php
namespace NewdichMail;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use NewdichSchema\Settings;

class Index{
    private $host = Settings::APP_SMTP;
    private $otpmail = Settings::APP_OTP_EMAIL;
    private $otpmailpass = Settings::APP_OTP_EMAIL_PASSWORD;
    private $mailport = Settings::APP_PORT;
    private $appname = Settings::APP_NAME;
    private $mailaddr = Settings::APP_SENDING_EMAIL;
    private $mailaddrpass = Settings::APP_SENDING_EMAIL_PASSWORD;


    public function sendOtp($subject, $body, $recipient){
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $this->host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->otpmail;
            $mail->Password   = $this->otpmailpass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int) $this->mailport;

            $mail->setFrom($this->otpmail, $this->appname);
            $mail->addAddress($recipient);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return json_encode(array("status"=>"success", "response"=>"OTP was delivered to $recipient"), JSON_PRETTY_PRINT);

        } catch (Exception $e) {
            return json_encode(array("status"=>"failed", "response"=>"OTP failed to deliver {$mail->ErrorInfo}"), JSON_PRETTY_PRINT);
        }
    }





    public function sendMail($subject, $body, $recipient){
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $this->host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->mailaddr;
            $mail->Password   = $this->mailaddrpass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int) $this->mailport;

            $mail->setFrom($this->mailaddr, $this->appname);
            $mail->addAddress($recipient);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return json_encode(array("status"=>"success", "response"=>"Mail was delivered to $recipient"), JSON_PRETTY_PRINT);

        } catch (Exception $e) {
            return json_encode(array("status"=>"failed", "response"=>"Mail failed to deliver {$mail->ErrorInfo}"), JSON_PRETTY_PRINT);
        }
    }
}
?>