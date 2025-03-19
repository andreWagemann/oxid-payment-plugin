<?php

namespace PaymentAG\PaymentModule\Helper;

use PaymentAG\PaymentModule\Model\PaymentMethods\Creditcard;
use PaymentAG\PaymentModule\Model\PaymentMethods\Invoice;
use PaymentAG\PaymentModule\Model\PaymentMethods\OnlineBankTransfer;
use PaymentAG\PaymentModule\Model\PaymentMethods\Paypal;
use PaymentAG\PaymentModule\Model\PaymentMethods\Sepa;

class Module {

    public static function isSupportedPayment($type): bool {
        return str_starts_with($type, "paymentag_");
    }

    public static function getSupportedPayments(): array {
        return [
            Invoice::IDENTIFIER => Invoice::class,
            Paypal::IDENTIFIER => Paypal::class,
            Sepa::IDENTIFIER => Sepa::class,
            Creditcard::IDENTIFIER => Creditcard::class,
            OnlineBankTransfer::IDENTIFIER => OnlineBankTransfer::class
        ];
    }

    public static function dd(...$params) {
        echo "<pre>";

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        echo "Called from: {$backtrace['file']} on line {$backtrace['line']}<br/><br/>";

        foreach($params as $param) {
            var_dump($param);
        }

        die();
    }
}