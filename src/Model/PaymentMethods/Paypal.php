<?php

namespace PaymentAG\PaymentModule\Model\PaymentMethods;

class Paypal extends Base {

    const IDENTIFIER = "paymentag_paypal";

    function getProviderIdentifier(): string {
        return "paypal";
    }
}