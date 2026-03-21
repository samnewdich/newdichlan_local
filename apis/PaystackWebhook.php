<?php
namespace NewdichApis;
use NewdichSchema\Platform;
use NewdichSchema\Migration;

class PaystackWebhook{
    private $event;
    private $planTable = Platform::PLANS_TABLE;
    private $usersTable = Platform::USERS_TABLE;
    private $table = Platform::PAYMENT_TABLE;
    public function __construct(array $event){
        $this->event = $event;
    }

    public function process(){
        $event = $this->event;
        $gateway ="paystack";
        if ($event['event'] === 'charge.success') {
            $data = $event['data'];
            $amountPaid = $data['amount'];
            $email = $data['customer']['email'] ?? '';
            $timePaid = $data["paid_at"];
            $reference = $data["reference"];
            $currentTime = strtotime($timePaid);
            
            //get plans and determine which he paid for
            //get the marchant_code from the user email
            //because the user email will be something like lan_123456_userhasedemail@newdich.tech
            //so explode() would work, the lan has index 0, 123456 has index 1, etc...
            //so the format for creating email for user will be lan_marchantcode_userhashedmac@newdich.tech
            $explodeEmail = explode('_', $email);
            $marchant_code = $explodeEmail[1];
            $emailAlone = $explodeEmail[2];
            $hashedMac = str_replace("@newdich.tech","", $emailAlone); //the hased mac is what you will use to compare with the user's hashed current mac
            
            //now get plans
            $plansToGet = [
                "marchant_code" => $marchant_code
            ];
            $offset = 0;
            $limit = 1000;
            
            $newPlanMigration = new Migration(null, $this->planTable);
            $getPlans = $newPlanMigration->get($plansToGet, $offset, $limit);
            $getPlansDec = json_decode($getPlans, true);
            if($getPlansDec["status"] ==="success"){
                $planResponse = $getPlansDec["response"];
                $planInSeconds;
                $plan;
                $quantity;
                $unit;
                
                for($i=0; $i < count($planResponse); $i++){
                    if((float) $planResponse[$i]["price"] === (float) $amountPaid){
                        $planb = $planResponse[$i]["plan"];
                        $plan = $planb;
                        $quantity = (int) preg_replace('/[^0-9]/', '', $planb); // removes non-digits
                        $unit = preg_replace('/[0-9]/', '', $planb);          // removes digits
                    }
                }
                
                if(strtolower($unit) ==="hour" || strtolower($unit) ==="hours"){
                    $planInSeconds = (int) $quantity * 60 * 60;
                }
                elseif(strtolower($unit) ==="day" || strtolower($unit) ==="days"){
                    $planInSeconds = (int) $quantity * 24 * 60 * 60;
                }
                elseif(strtolower($unit) ==="week" || strtolower($unit) ==="weeks"){
                    $planInSeconds = (int) $quantity * 7 * 24 * 60 * 60;
                }
                elseif(strtolower($unit) ==="month" || strtolower($unit) ==="months"){
                    $planInSeconds = (int) $quantity * 4 * 7 * 24 * 60 * 60;
                }
                
                $expiresAt = (int) $currentTime + (int) $planInSeconds;
                $subStatus ="active";
                
                //Now record to payment history and update users table
                $paymentToSave = [
                    "email" => $email,
                    "generated_account_number" =>"",
                    "generated_account_name" =>"",
                    "currency" =>"",
                    "status" => "success",
                    "transaction_id" => $reference,
                    "reference" => $reference,
                    "date_started" => $currentTime,
                    "date_completed" => $currentTime,
                    "mac" => "",
                    "hashed_mac" => $hashedMac,
                    "expires_at" => $expiresAt,
                    "ip" => "",
                    "plan" => $plan,
                    "hours_paid_for" => $planInSeconds, //in seconds
                    "gateway" => $paystack,
                    "marchant_code" => $marchant_code,
                    "fee" =>""
                ];
                $uniquecol ="reference";
                $uniqueval = $reference;
                $newMigration = new Migration(null, $this->table);
                $save = $newMigration->saveUnique($uniquecol, $uniqueval, $paymentToSave);
                $saveDec = json_decode($save, true);
                if($saveDec["status"] ==="success"){
                    //Now update the user
                    $dataToUpdate = [
                        "expires_at" => $expiresAt,
                        "sub_status" => $subStatus,
                    ];
                    
                    $condition = [
                        "hashed_mac" => $hashedMac,
                        "marchant_code"=>$marchant_code
                    ];
                    
                    $newUserMigration = new Migration(null, $this->usersTable);
                    $edit = $newUserMigration->edit($dataToUpdate, $condition);
                    http_response_code(200);
                    return $edit;
                }
                else{
                    http_response_code(401);
                    return $save;
                }
            }
            else{
                http_response_code(401);
                return $getPlans;
            }
        }
        else{
            http_response_code(200);
            return json_encode([
                "status"=>"failed",
                "response"=>"Transaction failed"
            ], JSON_PRETTY_PRINT);
        }
    }
}
?>