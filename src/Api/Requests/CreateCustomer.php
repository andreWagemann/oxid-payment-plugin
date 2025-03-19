<?php

namespace PaymentAG\PaymentModule\Api\Requests;

use PaymentAG\PaymentModule\Helper\Order;
use PaymentAG\PaymentModule\Model\OrderExtension;

class CreateCustomer extends Base {

    private OrderExtension $order;

    public function __construct(OrderExtension $order) {
        $this->order = $order;
    }

    function fire() {
        $country = Order::getIso3Country($this->order);

        $requestData = [
            'company' => null,
            'salutation' => strtolower($this->order->oxorder__oxbillsal->value) == "mr" ? "Herr" : "Frau",
            'firstName' => $this->order->oxorder__oxbillfname->value,
            'lastName' => $this->order->oxorder__oxbilllname->value,
            'email' => $this->order->oxorder__oxbillemail->value,
            'billingaddress' => [
                'street' => $this->order->oxorder__oxbillstreet->value,
                'number' => $this->order->oxorder__oxbillstreetnr->value,
                'zip' => $this->order->oxorder__oxbillzip->value,
                'city' => $this->order->oxorder__oxbillcity->value,
                'country' => $country
            ]
        ];

        $response = $this->fireRequest("/Customer", $requestData, true);

        return $response->customer->id ?? null;
    }
}