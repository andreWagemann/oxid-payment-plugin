INSERT IGNORE INTO `oxpayments` (`OXID`, `OXACTIVE`, `OXDESC`, `OXADDSUM`, `OXADDSUMTYPE`, `OXFROMAMOUNT`, `OXTOAMOUNT`, `OXSORT`)
VALUES
    ('paymentag_invoice', 1, 'Invoice', 0, 'abs', 0, 1000000, 10),
    ('paymentag_paypal', 1, 'Paypal', 0, 'abs', 0, 1000000, 11),
    ('paymentag_sepa', 1, 'SEPA', 0, 'abs', 0, 1000000, 12),
    ('paymentag_cc', 1, 'Creditcard', 0, 'abs', 0, 1000000, 13),
    ('paymentag_onlinebanktransfer', 1, 'Online Bank Transfer', 0, 'abs', 0, 1000000, 14);
