<?php

namespace PaymentAG\PaymentModule\Model\PaymentMethods;

class Sepa extends Base {

    const IDENTIFIER = "paymentag_sepa";

    function getProviderIdentifier(): string {
        return "debit";
    }
}