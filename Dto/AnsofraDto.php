<?php
namespace NewdichDto;
use NewdichMiddleware\Index;

class AnsofraDto{
    public $email;
    public $password;
    public $fullname;
    public $username;
    public $phone;
    public $country;
    public $region;
    public $city;
    public $address;
    public $zip_code;
    public $date_created;
    public $last_seen;
    public $picture;
    public $role;
    public $database_name;
    public $offset;
    public $limit;
    public $action;
    public $currency;
    public $hashed_mac;
    public $last_name;
    public $first_name;

    public $mac;
    public $expires_at;
    public $paid;
    public $active;
    public $current_time;
    
    //paystack webhook
    public $signature;
    public $computed;
    public $input;
    public $event;

    //marchant table
    public $marchant_id;
    public $marchant_code;
    public $state;
    public $status;
    public $account_type;
    public $business_name;
    public $fee_rate;
    public $refer_code;
    public $amount_paid;
    public $refer_by;

    //user table
    public $users_id;
    public $last_ip;
    public $device_name;
    public $total_spent;
    public $last_sub_plan;
    public $total_data_used;
    public $has_reserved_account;
    public $sub_status;
    public $has_table_been_updated_locally;
    public $live_now;

    //reserved table
    public $reserved_id;
    public $gateway;
    public $ip_used;
    public $account_name;
    public $account_number;
    public $bank;

    //payment table
    public $payment_id;
    public $amount;
    public $generated_account_number;
    public $generated_account_name;
    public $transaction_id;
    public $reference;
    public $date_started;
    public $date_completed;
    public $ip;
    public $plan;
    public $hours_paid_for;
    public $date_withdrawn;
    public $withdrawn_device;
    public $withdrawn_bank_details;
    public $has_been_updated_locally;
    public $fee; //fee to deduct from the money
    public $has_withdrawn_from_gateway;
    public $admin_who_withdrawn;
    public $date_withdrawn_paid;
    public $has_updated_request_withdraw_locally;
    public $has_updated_completed_withdraw_locally;


    //plans table
    public $plans_id;
    public $duration;
    public $quantity;
    public $price;
    public $discount;


    //Admins table
    public $admins_id;




    public function __construct(array $inData){
        $allProp = get_object_vars($this);
        foreach($allProp as $k => $v){
            $this->$k = isset($inData[$k]) ? $inData[$k] : '';
        }
    }
}
?>