<?php

namespace PaymentAG\PaymentModule\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController as OxidFrontendController;
use OxidEsales\Eshop\Core\Registry;

class FrontendController extends OxidFrontendController {

    protected function getUtils() {
        return Registry::getUtils();
    }

    public function getSession() {
        return Registry::getSession();
    }

    public function getSessionRedirectUrl() {
        return $this->getSession()->getVariable("paymentag_redirect_url");
    }

    public function getConfig() {
        return Registry::getConfig();
    }

    public function getShopUrl(): ?string {
        return $this->getConfig()->getShopUrl();
    }
}
