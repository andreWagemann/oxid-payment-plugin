<?php

namespace PaymentAG\PaymentModule\Model\PaymentMethods;

class OnlineBankTransfer extends Base {

    const IDENTIFIER = "paymentag_onlinebanktransfer";

    function getProviderIdentifier(): string {
        return "onlinebanktransfer";
    }
}