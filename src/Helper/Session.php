<?php

namespace PaymentAG\PaymentModule\Helper;

use OxidEsales\Eshop\Core\Registry;

class Session {

    private static function get($key) {
        return Registry::getSession()->getVariable($key);
    }

    public static function setPayError($errorCode) {
        Registry::getSession()->setVariable('payerror', $errorCode);
    }

    public static function getSessionChallenge() {
        return self::get("sess_challenge");
    }

    public static function deleteSessionChallenge() {
        return self::delete("sess_challenge");
    }

    private static function delete($key) {
        Registry::getSession()->deleteVariable($key);
    }

    public static function deleteIsRedirected() {
        self::delete("paymentag_is_redirected");
    }

    public static function addErrorToDisplay(\Exception $ex, $blFull = false, $useCustomDestination = false, $customDestination = "") {
        Registry::getUtilsView()->addErrorToDisplay($ex, $blFull, $useCustomDestination, $customDestination);
    }
}