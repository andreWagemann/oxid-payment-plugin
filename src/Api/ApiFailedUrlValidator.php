<?php

namespace PaymentAG\PaymentModule\Api;

use PaymentAG\PaymentModule\Helper\Config;

class ApiFailedUrlValidator extends ApiUrlValidatorService {

    public function __construct() {
        parent::__construct(Config::getFrontendErrorParams());
    }
}