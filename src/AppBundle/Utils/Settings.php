<?php

namespace AppBundle\Utils;

use Symfony\Component\Validator\Constraints as Assert;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;

class Settings
{
    public $brand_name;

    public $administrator_email;

    /**
     * @AssertPhoneNumber
     */
    public $phone_number;

    /**
     * @Assert\Regex("/^pk_test_[A-Za-z0-9]+/")
     */
    public $stripe_test_publishable_key;

    /**
     * @Assert\Regex("/^sk_test_[A-Za-z0-9]+/")
     */
    public $stripe_test_secret_key;

    /**
     * @Assert\Regex("/^ca_[A-Za-z0-9]+/")
     */
    public $stripe_test_connect_client_id;

    /**
     * @Assert\Regex("/^pk_live_[A-Za-z0-9]+/")
     */
    public $stripe_live_publishable_key;

    /**
     * @Assert\Regex("/^sk_live_[A-Za-z0-9]+/")
     */
    public $stripe_live_secret_key;

    /**
     * @Assert\Regex("/^ca_[A-Za-z0-9]+/")
     */
    public $stripe_live_connect_client_id;

    public $sms_enabled;

    public $sms_gateway;

    public $sms_gateway_config;

    /**
     * @Assert\Choice({"yes", "no"})
     */
    public $stripe_livemode;

    public $google_api_key;

    public $latlng;

    public $default_tax_category;

    public $currency_code;

    /**
     * @Assert\Choice({"yes", "no"})
     */
    public $enable_restaurant_pledges;
}
