<?php

namespace PaymentAG\PaymentModule\Model;

use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use PaymentAG\PaymentModule\Helper\Module;
use PaymentAG\PaymentModule\Helper\Session;
use PaymentAG\PaymentModule\Helper\Vars;
use PaymentAG\PaymentModule\Model\PaymentMethods\Base;

class PaymentGatewayExtension extends PaymentgatewayExtension_parent {

    /**
     * @param $dAmount
     * @param OrderExtension $oOrder
     * @return bool
     */
    public function executePayment($dAmount, &$oOrder): bool {
        if(!$oOrder->isPaymentAgPayment()) {
            Session::deleteIsRedirected();

            return parent::executePayment($dAmount, $oOrder);
        }

        $paymentMethods = Module::getSupportedPayments();
        $paymentId = $oOrder->getPaymentId();

        if (isset($paymentMethods[$paymentId])) {
            /** @var Base $method */
            $method = new $paymentMethods[$paymentId]();
            $redirectUrl = $method->getRedirectUrl($oOrder, $paymentId);

            if(strlen($redirectUrl) > 0) {
                $oOrder->oxorder__oxtransstatus = new Field(Vars::TRANSACTION_STATUS_PENDING);
                $oOrder->oxorder__pagpaymentstatus = new Field(Vars::PAYMENT_STATUS_STARTED);
                $oOrder->oxorder__oxfolder = new Field('ORDERFOLDER_NEW');
                $oOrder->save();

                Session::setIsRedirected();

                Registry::getUtils()->redirect($redirectUrl);
            }
        }

        return false;
    }
}