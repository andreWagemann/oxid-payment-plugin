<?php

namespace PaymentAG\PaymentModule\Helper;

use OxidEsales\Eshop\Core\Registry;

class Config {

    const KEY_CLIENTID = "paymentag_clientid";
    const KEY_SHAREDSECRET = "paymentag_shared_secret";
    const KEY_APIKEY = "paymentag_api_key";
    const KEY_FRONTEND_REQUEST_PARAMS = "paymentag_frontend_request_params";
    const KEY_FRONTEND_SUCCESS_PARAMS = "paymentag_frontend_success_params";
    const KEY_FRONTEND_ERROR_PARAMS = "paymentag_frontend_error_params";
    const KEY_CANCEL_MODE = "paymentag_cancel_mode";
    const KEY_PAYMENT_MODE = "paymentag_pay_mode";

    private static function get($key) {
        return Registry::getConfig()->getConfigParam($key);
    }

    public static function getClientId() {
        return self::get(self::KEY_CLIENTID);
    }

    public static function getSharedSecret() {
        return self::get(self::KEY_SHAREDSECRET);
    }

    public static function getApiKey() {
        return self::get(self::KEY_APIKEY);
    }

    public static function getFrontendRequestParams() {
        return self::get(self::KEY_FRONTEND_REQUEST_PARAMS);
    }

    public static function getFrontendSuccessParams() {
        return self::get(self::KEY_FRONTEND_SUCCESS_PARAMS);
    }

    public static function getFrontendErrorParams() {
        return self::get(self::KEY_FRONTEND_ERROR_PARAMS);
    }

    public static function getCancelMode() {
        return self::get(self::KEY_CANCEL_MODE);
    }

    public static function getPaymentMode() {
        return self::get(self::KEY_PAYMENT_MODE);
    }
}