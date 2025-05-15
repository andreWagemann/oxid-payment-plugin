<?php

namespace PaymentAG\PaymentModule\Helper;

use OxidEsales\Eshop\Core\Registry;

class Session {

    const KEY_IS_REDIRECTED = "paymentag_is_redirected";
    const KEY_SESSION_CHALLENGE = "sess_challenge";
    const KEY_PAYMENT_ID = "paymentid";

    private static function get($key) {
        Registry::getSession()->start();

        return Registry::getSession()->getVariable($key);
    }

    private static function set($key, $value) {
        Registry::getSession()->setVariable($key, $value);
    }

    private static function delete($key) {
        Registry::getSession()->deleteVariable($key);
    }

    private static function isSet($key) {
        return Registry::getSession()->hasVariable($key);
    }

    public static function setPayError($errorCode) {
        self::set('payerror', $errorCode);
    }

    public static function getSessionChallenge() {
        return self::get(self::KEY_SESSION_CHALLENGE);
    }

    public static function setSessionChallenge($value) {
        self::set(self::KEY_SESSION_CHALLENGE, $value);
    }

    public static function deleteSessionChallenge() {
        self::delete(self::KEY_SESSION_CHALLENGE);
    }

    public static function deleteIsRedirected() {
        self::delete(self::KEY_IS_REDIRECTED);
    }

    public static function setIsRedirected() {
        self::set(self::KEY_IS_REDIRECTED, "yes");
    }

    public static function isRedirected(): bool {
        return self::isSet(self::KEY_IS_REDIRECTED);
    }

    public static function addErrorToDisplay(\Exception $ex, $blFull = false, $useCustomDestination = false, $customDestination = "") {
        Registry::getUtilsView()->addErrorToDisplay($ex, $blFull, $useCustomDestination, $customDestination);
    }

    public static function setPaymentId($value) {
        self::set(self::KEY_PAYMENT_ID, $value);
    }

    public static function setBasket($basket) {
        Registry::getSession()->setBasket($basket);
    }

}