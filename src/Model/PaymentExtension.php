<?php

namespace PaymentAG\PaymentModule\Model;

use OxidEsales\Eshop\Application\Model\BasketItem;
use OxidEsales\EshopCommunity\Core\Registry;
use PaymentAG\PaymentModule\Helper\Config;
class PaymentExtension extends PaymentExtension_parent {

    /**
     * Zusatzkosten für Zahlungsart vom Typ paymentag berechnen
     */
    public function getPrice()
    {
        $price = parent::getPrice();
        // Warenkorb holen
        $basket = \OxidEsales\Eshop\Core\Registry::getSession()->getBasket();
        $basketSum = $basket->getPrice()->getBruttoPrice();

        $paymentId = $this->getId();
        if( str_starts_with($paymentId,'paymentag_') && $price->getBruttoPrice() <= 0) {
            $extraFee = 0;

            if ((int)Config::getPaymentAddPercent() > 0) {
                $extraFee = $basketSum * ((int)Config::getPaymentAddPercent() / 100);
            }

            if ($extraFee > 0) {
                $price->add($extraFee);
            }
        }

        return $price;
    }
}