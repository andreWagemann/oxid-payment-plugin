<?php

namespace PaymentAG\PaymentModule\Model\PaymentMethods;

class Creditcard extends Base {

    const IDENTIFIER = "paymentag_cc";

    function getProviderIdentifier(): string {
        return "creditcard";
    }
}