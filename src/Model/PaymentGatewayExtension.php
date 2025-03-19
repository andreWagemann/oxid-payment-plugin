<?php

namespace PaymentAG\PaymentModule\Model;

use OxidEsales\Eshop\Core\Registry;
use PaymentAG\PaymentModule\Helper\Module;
use PaymentAG\PaymentModule\Model\PaymentMethods\Base;

class PaymentGatewayExtension extends PaymentgatewayExtension_parent {

    /**
     * @param $dAmount
     * @param OrderExtension $oOrder
     * @return bool
     */
    public function executePayment($dAmount, &$oOrder): bool {
        if(!$oOrder->isPaymentAgPayment()) {
            return parent::executePayment($dAmount, $oOrder);
        }

        $paymentMethods = Module::getSupportedPayments();
        $paymentId = $oOrder->getPaymentId();

        if (isset($paymentMethods[$paymentId])) {
            /** @var Base $method */
            $method = new $paymentMethods[$paymentId]();
            $redirectUrl = $method->getRedirectUrl($oOrder, $paymentId);

            if(strlen($redirectUrl) > 0) {
                Registry::getUtils()->redirect($redirectUrl);
            }
        }

        return false;
    }
}