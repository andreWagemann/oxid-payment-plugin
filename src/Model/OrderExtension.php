<?php

namespace PaymentAG\PaymentModule\Model;

use OxidEsales\Eshop\Application\Model\BasketItem;
use OxidEsales\EshopCommunity\Core\Registry;
use PaymentAG\PaymentModule\Helper\Module;

class OrderExtension extends OrderExtension_parent {

    public function isPaymentAgPayment(): bool {
        return Module::isSupportedPayment($this->getPaymentId());
    }

    public function getPaymentId(): string {
        return $this->oxorder__oxpaymenttype->value;
    }

    public function paymentagPrepareFinalizeOrder() {
        $session = Registry::getSession();
        $oBasket = $session->getBasket();

        if($oBasket) {
            $oBasket = $this->recreateBasket();
            $session->setBasket($oBasket);
        }

        $this->_oBasket = $oBasket;

        $this->finalizeReturnMode = true;
        $this->finishOrderReturnMode = true;
    }

    public function recreateBasket(): ?\OxidEsales\Eshop\Application\Model\Basket {
        $this->reloadDiscount(true);
        $oBasket = $this->_getOrderBasket();

        // add this order articles to virtual basket and recalculates basket
        $aItems = $this->getOrderArticles(true);

        /** @var BasketItem $item */
        foreach($aItems as $item) {
            $item->getArticle(false, null, true);
        }

        $this->_addOrderArticlesToBasket($oBasket, $aItems);

        // recalculating basket
        $oBasket->calculateBasket(true);

        Registry::getSession()->setVariable('sess_challenge', $this->getId());
        Registry::getSession()->setVariable('paymentid', $this->oxorder__oxpaymenttype->value);
        Registry::getSession()->setBasket($oBasket);

        return $oBasket;
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