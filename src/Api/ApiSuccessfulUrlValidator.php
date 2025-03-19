<?php

namespace PaymentAG\PaymentModule\Api;

use PaymentAG\PaymentModule\Helper\Config;

class ApiSuccessfulUrlValidator extends ApiUrlValidatorService {

    public function __construct() {
        parent::__construct(Config::getFrontendSuccessParams());
    }
}