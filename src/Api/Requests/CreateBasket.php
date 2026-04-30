<?php

namespace PaymentAG\PaymentModule\Api\Requests;

use OxidEsales\Eshop\Application\Model\OrderArticle;
use OxidEsales\Eshop\Core\Price;
use PaymentAG\PaymentModule\Helper\Config;
use PaymentAG\PaymentModule\Helper\Order;
use PaymentAG\PaymentModule\Model\OrderExtension;

class CreateBasket extends Base {

    private OrderExtension $order;

    public function __construct(OrderExtension $order) {
        $this->order = $order;
    }

    function fire() {
        $items = [];
        $currency = $this->order->getOrderCurrency()->name;
        $totalOrderSumOrg = Order::getPriceInCents($this->order->getTotalOrderSum());
        $totalOrderSumNew = $totalOrderSumOrg;
         if ((int)Config::getPaymentAddPercent() > 0) {
             $totalOrderSumNew = ceil($totalOrderSumOrg * (((int)Config::getPaymentAddPercent() / 100) + 1));
         }

        foreach($this->order->getOrderArticles() as $orderArticle) {
            $price = Order::getPriceInCents($orderArticle->oxorderarticles__oxbrutprice->value);
            $priceUnit = Order::getPriceInCents($orderArticle->oxorderarticles__oxbprice->value);
            $qty = round($price / $priceUnit);
            $tax = Order::getPriceInCents($orderArticle->oxorderarticles__oxvatprice->value);
            $taxRate = $orderArticle->oxorderarticles__oxvat->value;

            /** @var OrderArticle $orderArticle */
            $items[] = [
                "articleCode" => $orderArticle->oxorderarticles__oxartnum->value,
                "description" => $orderArticle->oxorderarticles__oxtitle->value,
                "quantity" => $qty,
                "pricePerUnitGross" => (int)$priceUnit,
                "currency" => $currency,
                "taxAmount" => (int)$tax,
                "taxPercent" => $taxRate
            ];
        }
        if ((int)Config::getPaymentAddPercent() > 0 ) {
            $items[0]['pricePerUnitGross'] += ($totalOrderSumNew - $totalOrderSumOrg);
            $items[0]['taxAmount'] = ceil($items[0]['pricePerUnitGross'] / (100 + $items[0]['taxPercent']) * $items[0]['taxPercent']);
        }

        /** @var Price $shipping */
        $shipping = $this->order->getOrderDeliveryPrice();
        $shippingPrice = Order::getPriceInCents($shipping->getBruttoPrice());
        $shippingTax = Order::getPriceInCents($shipping->getVatValue());
        $shippingTaxRate = $shipping->getVat();

        $items[] = [
            "articleCode" => "shipping-costs",
            "description" => "Shipping",
            "quantity" => 1,
            "pricePerUnitGross" => (int)$shippingPrice,
            "currency" => $currency,
            "taxAmount" => (int)$shippingTax,
            "taxPercent" => $shippingTaxRate
        ];

        $voucherDiscount = -Order::getPriceInCents($this->order->oxorder__oxvoucherdiscount->value);
        $voucherTaxRate = 19;
        $voucherTax = Order::getPriceInCents(number_format($voucherDiscount / (100 + $voucherTaxRate) * $voucherTaxRate, 2) / 100);

        $items[] = [
            "articleCode" => "vouchers",
            "description" => "Vouchers",
            "quantity" => 1,
            "pricePerUnitGross" => (int)$voucherDiscount,
            "currency" => $currency,
            "taxAmount" => (int)$voucherTax,
            "taxPercent" => $voucherTaxRate
        ];

        $requestData = [
            "items" => $items
        ];

        $response = $this->fireRequest("/basket", $requestData, true);

        return $response->id ?? null;
    }
}