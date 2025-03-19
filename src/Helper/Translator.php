<?php

namespace PaymentAG\PaymentModule\Helper;

use OxidEsales\Eshop\Core\Registry;

class Translator {

    public static function getTranslatedString($key) {
        return Registry::getLang()->translateString($key);
    }
}