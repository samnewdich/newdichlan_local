<?php
namespace NewdichSchema;

class Platform{
    public const USERS_TABLE ="users";
    public const USERS_TABLE_COLUMNS =[
        "users_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY",
        "email VARCHAR(255) NOT NULL",
        "fullname VARCHAR(255)",
        "mac VARCHAR(255)",
        "hashed_mac VARCHAR(255)",
        "expires_at VARCHAR(255)",
        "last_ip VARCHAR(255)",
        "device_name VARCHAR(255)",
        "total_spent VARCHAR(255)",
        "last_sub_plan VARCHAR(255)",
        "total_data_used VARCHAR(255)",
        "date_created VARCHAR(255)", 
        "last_seen VARCHAR(255)",
        "picture TEXT",
        "username VARCHAR(255)",
        "account_type VARCHAR(255)",
        "phone VARCHAR(255)",
        "has_reserved_account VARCHAR(255)",
        "refer_code VARCHAR(255)",
        "refer_by VARCHAR(255)",
        "sub_status VARCHAR(255)",
        "has_table_been_updated_locally VARCHAR(255)",
        "marchant_code VARCHAR(255)",
        "live_now VARCHAR(255)"
    ];

    public const RESERVED_TABLE ="reserved";
    public const RESERVED_TABLE_COLUMNS =[
        "reserved_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY",
        "email VARCHAR(255) NOT NULL",
        "fullname VARCHAR(255)",
        "mac VARCHAR(255)",
        "gateway VARCHAR(255)",
        "ip_used VARCHAR(255)",
        "account_name VARCHAR(255)",
        "account_number VARCHAR(255)",
        "bank VARCHAR(255)",
        "date_created VARCHAR(255)", 
        "account_type VARCHAR(255)",
        "status VARCHAR(255)",
        "marchant_code VARCHAR(255)"
    ];


    //you can have as many tables as you want
    public const MARCHANT_TABLE ="marchant";
    public const MARCHANT_TABLE_COLUMNS = [
        "marchant_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY",
        "email VARCHAR(255) NOT NULL",
        "password VARCHAR(255) NOT NULL",
        "fullname VARCHAR(255) NOT NULL",
        "phone VARCHAR(255) NOT NULL",
        "marchant_code VARCHAR(255) NOT NULL",
        "date_created VARCHAR(255) NOT NULL",
        "address VARCHAR(255) NOT NULL",
        "city VARCHAR(255) NOT NULL",
        "state VARCHAR(255) NOT NULL",
        "country VARCHAR(255) NOT NULL",
        "status VARCHAR(255)",
        "account_type VARCHAR(255)",
        "business_name VARCHAR(255)",
        "fee_rate VARCHAR(255)",
        "refer_code VARCHAR(255)",
        "amount_paid VARCHAR(255)",
        "refer_by VARCHAR(255)"
    ];


    public const PAYMENT_TABLE ="payment";
    public const PAYMENT_TABLE_COLUMNS = [
        "payment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY",
        "email VARCHAR(255)",
        "amount VARCHAR(255)",
        "generated_account_number VARCHAR(255)",
        "generated_account_name VARCHAR(255)",
        "currency VARCHAR(255)",
        "status VARCHAR(255)",
        "transaction_id VARCHAR(255)",
        "reference VARCHAR(255)",
        "date_started VARCHAR(255)",
        "date_completed VARCHAR(255)",
        "mac VARCHAR(255)",
        "hashed_mac VARCHAR(255)",
        "expires_at VARCHAR(255)",
        "ip VARCHAR(255)",
        "plan VARCHAR(255)",
        "hours_paid_for VARCHAR(255)",
        "date_withdrawn VARCHAR(255)",
        "withdrawn_device VARCHAR(255)",
        "withdrawn_bank_details VARCHAR(255)",
        "gateway VARCHAR(255)",
        "marchant_code VARCHAR(255)",
        "has_been_updated_locally VARCHAR(255)",
        "fee VARCHAR(255)", //fee to deduct from the money
        "has_withdrawn_from_gateway VARCHAR(255)",
        "admin_who_withdrawn VARCHAR(255)",
        "date_withdrawn_paid VARCHAR(255)",
        "has_updated_request_withdraw_locally VARCHAR(255)",
        "has_updated_completed_withdraw_locally VARCHAR(255)",
    ];
    
    
    public const PLANS_TABLE ="plans";
    public const PLANS_TABLE_COLUMNS =[
        "plans_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY",
        "plan VARCHAR(255) NOT NULL",
        "duration VARCHAR(255) NOT NULL",
        "quantity VARCHAR(255)",
        "price VARCHAR(255) NOT NULL",
        "currency VARCHAR(255) NOT NULL",
        "discount VARCHAR(255)",
        "marchant_code VARCHAR(255)"
    ];

    //ADMINS THAT THE MARCHANTS CAN HAVE
    public const ADMINS_TABLE ="admins";
    public const ADMINS_TABLE_COLUMNS =[
        "admins_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY",
        "email VARCHAR(255) NOT NULL",
        "fullname VARCHAR(255) NOT NULL",
        "password VARCHAR(255) NOT NULL",
        "country VARCHAR(255) NOT NULL",
        "region VARCHAR(255)",
        "city VARCHAR(255)",
        "address VARCHAR(255)",
        "zip_code VARCHAR(255)",
        "phone VARCHAR(255)",
        "date_created VARCHAR(255)", 
        "last_seen VARCHAR(255)",
        "picture TEXT",
        "username VARCHAR(255)",
        "role VARCHAR(255) NOT NULL",
        "marchant_code VARCHAR(255)"
    ];

}
?>