<?php

namespace PaymentAG\PaymentModule\Api\Requests;

use PaymentAG\PaymentModule\Helper\Config;
use PaymentAG\PaymentModule\Helper\Order;
use PaymentAG\PaymentModule\Model\OrderExtension;
use PaymentAG\PaymentModule\Model\PaymentMethods\OnlineBankTransfer;

class CreateRedirectUrl extends Base {

    private OrderExtension $order;
    private string $customerId;
    private string $basketId;
    private string $paymentId;
    private string $providerIdentifier;

    public function __construct(OrderExtension $order, $customerId, $basketId, $paymentId, $providerIdentifier) {
        $this->order = $order;
        $this->customerId = $customerId;
        $this->basketId = $basketId;
        $this->paymentId = $paymentId;
        $this->providerIdentifier = $providerIdentifier;
    }

    function fire() {
        $country = Order::getIso3Country($this->order);

        $returnUrl = $this->getShopUrl() . '/index.php?cl=order&fnc=handlePaymentAgReturn';
        $requestData = [
            'redirectParameters' => [
                'successUrl' => $returnUrl,
                'failUrl' => $returnUrl
            ],
            'clientID' => Config::getClientId(),
            'paymentKey' => $this->providerIdentifier,
            'paymentmode' => Config::getPaymentMode(),
            'amount' => Order::getPriceInCents($this->order->getTotalOrderSum()),
            'currency' => $this->order->getOrderCurrency()->name,
            'referenceID' => rand(1000,9999) . date("YmdHis"),
            'email' => $this->order->oxorder__oxbillemail->value,
            'country' => $country,
            'ids' => [
                'customerID' => $this->customerId,
                'basketID' => $this->basketId
            ]
        ];

        if($this->paymentId == OnlineBankTransfer::IDENTIFIER || $requestData["paymentmode"] === "purchase") {
            unset($requestData["paymentmode"]);
        }

        return $this->fireRequest("/Payment", $requestData);
    }
}