<?php


namespace PaymentAG\PaymentModule\Api;

use OxidEsales\Eshop\Core\Registry;
use PaymentAG\PaymentModule\Helper\Config;

abstract class ApiUrlValidatorService {

    private array $values = [];
    private bool $hashValid = false;
    private string $url = "";
    private string $hash = "";
    private array $order = [];
    private string $sharedSecret = "";
    private string $baseUrl;

    function __construct($order) {
        $this->order = $order;
        $this->sharedSecret = Config::getSharedSecret();
        $this->baseUrl = "https://frontend.payment-transaction.net/payment.aspx?";
    }

    protected function getConfigParam($key, $default = "") {
        return Registry::getConfig()->getConfigParam($key);
    }

    public function setSecret($secret) {
        $this->sharedSecret = $secret;
    }

    public function set($name, $value) {
        $lowercaseName = strtolower($name);

        if(!in_array($lowercaseName, $this->order)) {
            return;
        }

        $this->values[$lowercaseName] = $value;
        $this->hashValid = false;
    }

    public function get($name, $default = "") {
        $lowercase_name = strtolower($name);
        if(array_key_exists($lowercase_name, $this->values)) {
            return $this->values[$lowercase_name];
        } else {
            return $default;
        }
    }

    public function getValues() {
        return $this->values;
    }

    public function getUrl() {
        if($this->hashValid == false) {
            $this->generateUrlHash();
        }
        return $this->url;
    }

    public function getHash() {
        if($this->hashValid == false) {
            $this->generateUrlHash();
        }
        return $this->hash;
    }

    public function generateUrlHash() {
        $http_args = array();
        $cleartext = "";
        foreach($this->order as $item) {
            if(array_key_exists($item, $this->values)) {
                $http_args[$item] = $this->values[$item];
                $cleartext .= $this->values[$item];
            }
        }

        $hash = strtoupper(sha1($cleartext . $this->sharedSecret));  //build url
        $http_args["hash"] = $hash;

        $this->url = $this->baseUrl . http_build_query($http_args);
        $this->hash = $hash;
        $this->hashValid = true;
    }
}