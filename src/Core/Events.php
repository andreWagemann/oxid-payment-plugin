<?php

namespace PaymentAG\PaymentModule\Core;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;

class Events {

    public static function onActivate() {
        self::executeSqlFromFile(__DIR__ . '/../../migrations/install.sql');
        self::executeSqlFromFile(__DIR__ . '/../../migrations/order.sql');
    }

    public static function onDeactivate() {
        
    }

    private static function executeSqlFromFile($filePath) {
        try {
            $db = DatabaseProvider::getDb();
            $sql = file_get_contents($filePath);
            $db->execute($sql);
        } catch(DatabaseConnectionException|DatabaseErrorException $e) {

        }
    }
}
