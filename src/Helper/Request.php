<?php

namespace PaymentAG\PaymentModule\Helper;

use OxidEsales\Eshop\Core\Registry;

class Request {

    public static function getParameter($name, $default = "") {
        return Registry::getRequest()->getRequestParameter($name, $default);
    }

    public static function getEscapedParameter($key) {
        return Registry::getRequest()->getRequestEscapedParameter($key);
    }

    public static function getOrdernumber() {
        return self::getEscapedParameter("onr");
    }

    public static function getShopUrl(): string {
        return Registry::getConfig()->getShopUrl();
    }

    public static function doRedirect($url) {
        Registry::getUtils()->redirect($url);
    }
}