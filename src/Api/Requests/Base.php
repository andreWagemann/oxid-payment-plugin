<?php

namespace PaymentAG\PaymentModule\Api\Requests;

use OxidEsales\Eshop\Core\Registry;
use PaymentAG\PaymentModule\Helper\Config;
use PaymentAG\PaymentModule\Helper\Logger;

abstract class Base {

    const API_BASE_URL = "https://api.payment-transaction.net/api/v1";

    abstract function fire();

    protected function fireRequest($url, $requestData, $isPlain = false) {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => self::API_BASE_URL . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($requestData),
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "content-type: application/json",
                "x-api-key: " . Config::getApiKey()
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        Logger::writeApiCall($url, $requestData, json_decode($response));

        if(strlen($err) > 0) {
            return "";
        }

        if($isPlain) {
            $response = json_decode($response, false);

            if(property_exists($response, "errors") || (property_exists($response, "status") && str_starts_with($response->status, "4"))) {
                return null;
            }

            return $response;
        }

        $response = json_decode($response, false);

        $wasSuccessful = $response->success ?? false;

        if($wasSuccessful) {
            return $response->paymentUrl ?? "";
        }

        return "";
    }

    protected function getShopUrl(): ?string {
        return Registry::getConfig()->getShopUrl();
    }
}