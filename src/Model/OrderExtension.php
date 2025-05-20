<?php

namespace PaymentAG\PaymentModule\Model;

use OxidEsales\Eshop\Application\Model\BasketItem;
use OxidEsales\EshopCommunity\Core\Registry;
use PaymentAG\PaymentModule\Helper\Module;
use PaymentAG\PaymentModule\Helper\Session;
use PaymentAG\PaymentModule\Helper\Vars;

class OrderExtension extends OrderExtension_parent {

    public function __construct() {
        parent::__construct();

        $this->addFieldName(Vars::ORDER_COLUMN_PAYMENT_DETAILS);
        $this->addFieldName(Vars::ORDER_COLUMN_PAYMENT_STATUS);
    }


    public function isPaymentAgPayment(): bool {
        return Module::isSupportedPayment($this->getPaymentId());
    }

    public function getPaymentId(): string {
        return $this->oxorder__oxpaymenttype->value;
    }

    public function paymentagPrepareFinalizeOrder() {
        $session = Registry::getSession();
        $oBasket = $session->getBasket();

        $this->_oBasket = $oBasket;

        $this->finalizeReturnMode = true;
        $this->finishOrderReturnMode = true;
    }

    public function setOrdernumber() {
        if(!$this->oxorder__oxordernr->value) {
            if (!empty($this->oxorder__oxordernr->value)) {
                return true;
            }

            return parent::_setNumber();
        }
    }
}