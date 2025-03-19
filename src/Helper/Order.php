<?php

namespace PaymentAG\PaymentModule\Helper;

use OxidEsales\Eshop\Application\Model\Voucher;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use PaymentAG\PaymentModule\Model\OrderExtension;

class Order {

    public static function getIso3Country(OrderExtension $oOrder): string {
        try {
            $db = DatabaseProvider::getDb();
            $sCountryId = $oOrder->oxorder__oxbillcountryid->value;
            return $db->getOne(sprintf("SELECT oxisoalpha3 FROM oxcountry WHERE oxid = '%s'", $sCountryId));
        } catch(DatabaseConnectionException $e) {
            return "EUR";
        }
    }

    public static function getPriceInCents($amount) {
        $amount = sprintf("%.2f", $amount);

        if(!str_contains($amount, ".")) {
            $amount .= ".00";
        }

        return str_replace(".", "", $amount);
    }

    public static function getOrder(): ?OrderExtension {
        $sOrderId = Session::getSessionChallenge();
        $order = self::loadOrder($sOrderId);

        if($order === null) {
            $sOrderNumber = Request::getOrdernumber();
            $db = DatabaseProvider::getDb();
            $sOrderId = $db->getOne("SELECT oxid FROM oxorder where OXORDERNR = '$sOrderNumber'");

            return self::loadOrder($sOrderId);
        }

        return $order;
    }

    private static function loadOrder($sOrderId): ?OrderExtension {
        if(!empty($sOrderId)) {
            /** @var OrderExtension $oOrder */
            $oOrder = oxNew(OrderExtension::class);
            $oOrder->load($sOrderId);

            if($oOrder->isLoaded() === true) {
                return $oOrder;
            }
        }

        return null;
    }

    public static function releaseVoucher(OrderExtension $oOrder) {
        $voucherList = $oOrder->getVoucherList();

        if(!empty($voucherList)) {
            /** @var Voucher $oVoucher */
            foreach($voucherList as $oVoucher) {
                $oVoucher->oxvouchers__oxorderid->setValue(null);
                $oVoucher->save();
            }
        }
    }
}