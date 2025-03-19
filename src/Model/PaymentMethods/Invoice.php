<?php

namespace PaymentAG\PaymentModule\Model\PaymentMethods;

class Invoice extends Base {

    const IDENTIFIER = "paymentag_invoice";

    function getProviderIdentifier(): string {
        return "volksbank";
    }
}