<?php

namespace PaymentAG\PaymentModule\Model\PaymentMethods;

use OxidEsales\Eshop\Core\Field;
use PaymentAG\PaymentModule\Api\Requests\CreateBasket;
use PaymentAG\PaymentModule\Api\Requests\CreateRedirectUrl;
use PaymentAG\PaymentModule\Api\Requests\CreateCustomer;
use PaymentAG\PaymentModule\Helper\Vars;
use PaymentAG\PaymentModule\Model\OrderExtension;

abstract class Base {

    public function getRedirectUrl($order, $paymentId): string {
        $customerId = $this->createCustomer($order);
        $basketId = $this->createBasket($order);

        if(is_null($customerId)) {
            // TODO add frontend warning

            return false;
        }

        if(is_null($basketId)) {
            // TODO add frontend warning

            return false;
        }

        $this->setOrderVars($order);
        $order->setOrdernumber();

        $createRedirectUrl = new CreateRedirectUrl($order, $customerId, $basketId, $paymentId, $this->getProviderIdentifier());

        return $createRedirectUrl->fire();
    }

    abstract function getProviderIdentifier(): string;

    private function setOrderVars(OrderExtension $order) {
        $order->oxorder__oxtransstatus = new Field(Vars::TRANSACTION_STATUS_PENDING);
        $order->oxorder__cdpaymentstatus = new Field(Vars::PAYMENT_STATUS_STARTED);
        $order->oxorder__oxfolder = new Field('ORDERFOLDER_NEW');
        $order->save();
    }

    private function createCustomer(OrderExtension $order) {
        $createCustomer = new CreateCustomer($order);

        return $createCustomer->fire();
    }

    private function createBasket(OrderExtension $order) {
        $createBasket = new CreateBasket($order);

        return $createBasket->fire();
    }


}