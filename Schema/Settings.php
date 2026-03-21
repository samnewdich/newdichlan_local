<?php
namespace NewdichSchema;

class Settings{
    public const DOC_ROOT = DOC_ROOT;
    public const ROOT_DIRECTORY = ROOT_DIRECTORY;
    public const APP_VERSION = APP_VERSION;
    public const APP_NAME = APP_NAME;
    public const APP_URL = APP_URL;
    public const APP_TITLE = APP_TITLE;
    public const APP_DESCRIPTION = APP_DESCRIPTION;
    public const APP_SMTP = APP_SMTP;
    public const APP_PORT = APP_PORT;
    public const APP_OTP_EMAIL = APP_OTP_EMAIL;
    public const APP_OTP_EMAIL_PASSWORD = APP_OTP_EMAIL_PASSWORD;
    public const APP_SENDING_EMAIL = APP_SENDING_EMAIL;
    public const APP_SENDING_EMAIL_PASSWORD = APP_SENDING_EMAIL_PASSWORD;
    
    //FOR MAILING VIA SENDGRID
    public const SENDGRID_API_KEY = SENDGRID_API_KEY;
    public const SENDGRID_CUSTOMIZED_DOMAIN_EMAIL = SENDGRID_CUSTOMIZED_DOMAIN_EMAIL;
    public const SENDGRID_MAILING_ENDPOINT = SENDGRID_MAILING_ENDPOINT;

    //FOR MAILING VIA MAILGUN
    public const MAILGUN_API_KEY = MAILGUN_API_KEY;
    public const MAILGUN_CUSTOMIZED_DOMAIN_EMAIL = MAILGUN_CUSTOMIZED_DOMAIN_EMAIL;
    public const MAILGUN_VERIFIED_DOMAIN = MAILGUN_VERIFIED_DOMAIN;
    public const MAILGUN_MAILING_ENDPOINT = MAILGUN_MAILING_ENDPOINT;

    //for mailersend
    public const MAILERSEND_API_KEY = MAILERSEND_API_KEY;
    public const MAILERSEND_CUSTOMIZED_DOMAIN_EMAIL = MAILERSEND_CUSTOMIZED_DOMAIN_EMAIL;
    public const MAILERSEND_VERIFIED_DOMAIN = MAILERSEND_VERIFIED_DOMAIN;
    public const MAILERSEND_MAILING_ENDPOINT = MAILERSEND_MAILING_ENDPOINT;
    

    //for annotations
    public const APP_ANNOTATION_TITLE = APP_ANNOTATION_TITLE;
    public const SRC_ANNOTATION_TITLE = SRC_ANNOTATION_TITLE;

    //set server configuration
    public const SERVER = SERVER;
    public const SERVER_USER = SERVER_USER;
    public const SERVER_DB = SERVER_DB;
    public const SERVER_PASS = SERVER_PASS;

    //other configuration
    public const DOMAIN_NAME = DOMAIN_NAME;

    //JWT Configuratiom
    public const AUTH_KEY = AUTH_KEY;
    public const JWT_KEY = JWT_KEY;
    public const JWT_EXPIRY = JWT_EXPIRY;
    public const JWT_SECURE_LEVEL = JWT_SECURE_LEVEL;
    public const JWT_SAMESITE = JWT_SAMESITE;
    public const JWT_HASH_ALGORITHM = JWT_HASH_ALGORITHM;

    //for redis caching
    public const REDIS_SERVER_IP = REDIS_SERVER_IP;
    public const REDIS_SERVER_PORT = REDIS_SERVER_PORT;
    public const REDIS_AUTH_PASSWORD = REDIS_AUTH_PASSWORD;

    //for file and uploading
    public const UPLOAD_DIRECTORY = UPLOAD_DIRECTORY;
    public const MAX_UPLOAD_SIZE = MAX_UPLOAD_SIZE;

    //for paystack
    public const PAYSTACK_SECRET_KEY = PAYSTACK_SECRET_KEY;
    public const PAYSTACK_PUBLIC_KEY = PAYSTACK_PUBLIC_KEY;
    public const PAYSTACK_VERIFICATION_LINK = PAYSTACK_VERIFICATION_LINK;
    public const PAYSTACK_GENERATE_RESERVED_LINK = PAYSTACK_GENERATE_RESERVED_LINK;

    //for exchangerateapi
    public const EXCHANGERATE_APIKEY = EXCHANGERATE_APIKEY;
    public const EXCHANGERATE_LINK = EXCHANGERATE_LINK;

    public const MARCHANT_CODE = MARCHANT_CODE;
}
?>