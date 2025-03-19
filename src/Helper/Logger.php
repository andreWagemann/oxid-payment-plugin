<?php

namespace PaymentAG\PaymentModule\Helper;

use OxidEsales\Eshop\Core\Registry;

class Logger {

    public static function writeApiCall($url, $request, $response): void {
        $message = sprintf("URL: %s\r\n\r\nRequest\r\n%s\r\n\r\nResponse\r\n%s\r\n\r\n------------", $url, json_encode($request, JSON_PRETTY_PRINT), json_encode($response, JSON_PRETTY_PRINT));

        self::writeLog($message, "api-calls");
    }

    public static function writeProviderResponse($response): void {
        $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $message = sprintf("URL: %s\r\n\r\nResponse\r\n%s\r\n\r\n------------", $url, json_encode($response, JSON_PRETTY_PRINT));

        self::writeLog($message, "payment-provider-responses");
    }

    /**
     * Use own filehandling instead of oxid logging, because otherwise we cant put newlines into it
     *
     * @param $message
     * @param $folder
     * @return void
     */
    private static function writeLog($message, $folder) {
        $filepath = sprintf("%s/paymentag-paymentmodule/%s/%s/%s.log", Registry::getConfig()->getLogsDir(), $folder, date("Y/m/d"), date("Y-m-d-H-i-s"));
        @mkdir(dirname($filepath), 0777, true);

        file_put_contents($filepath, $message, FILE_APPEND);
    }
}