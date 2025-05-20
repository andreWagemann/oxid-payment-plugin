<?php

namespace PaymentAG\PaymentModule\Controller;

use OxidEsales\Eshop\Core\Field;
use PaymentAG\PaymentModule\Api\ApiFailedUrlValidator;
use PaymentAG\PaymentModule\Api\ApiSuccessfulUrlValidator;
use PaymentAG\PaymentModule\Api\ApiUrlValidatorService;
use PaymentAG\PaymentModule\Exception\PaymentagException;
use PaymentAG\PaymentModule\Exception\PaymentagPaymentException;
use PaymentAG\PaymentModule\Helper\Config;
use PaymentAG\PaymentModule\Helper\Logger;
use PaymentAG\PaymentModule\Helper\Order;
use PaymentAG\PaymentModule\Helper\Request;
use PaymentAG\PaymentModule\Helper\Session;
use PaymentAG\PaymentModule\Helper\Translator;
use PaymentAG\PaymentModule\Helper\Vars;
use PaymentAG\PaymentModule\Model\OrderExtension;

class OrderController extends OrderController_parent {

    public function render(): string {
        $sSessChallenge = Session::getSessionChallenge();
        $wasRedirected = Session::isRedirected();

        if (!empty($sSessChallenge) && $wasRedirected === true) {
            $oOrder = oxNew(OrderExtension::class);

            if ($oOrder->load($sSessChallenge) === true) {
                if ($oOrder->oxorder__oxtransstatus->value !== Vars::TRANSACTION_STATUS_OK) {
                    $oOrder->delete();
                }
            }
        }

        Session::deleteIsRedirected();

        return parent::render();
    }

    public function handlePaymentAgReturn() {
        Logger::writeProviderResponse($_REQUEST);

        try {
            $order = Order::getOrder();

            if(is_null($order)) {
                throw new PaymentagException(Translator::getTranslatedString(PaymentagException::ERROR_MESSAGE_ORDER_NOT_FOUND));
            }

            Order::releaseVoucher($order);

            if($order->isPaymentAgPayment()) {
                $validator = $this->isRequestValid($_REQUEST);

                if(is_null($validator)) {
                    throw new PaymentagPaymentException(PaymentagPaymentException::ERROR_MESSAGE_REQUEST_INVALID);
                }

                Session::deleteIsRedirected();

                $aResult = $this->handleRequestValues($validator, $order);

                $details = [
                    "request" => $_REQUEST,
                    "response" => $aResult
                ];

                $order->oxorder__pagpaymentdetails = new Field(json_encode($details));
                $order->save();

                if($aResult["success"] === false) {
                    $status = Vars::PAYMENT_STATUS_CANCELED;
                    $sErrorIdent = 'PAYMENTAG_ERROR_SOMETHING_WENT_WRONG';

                    if($aResult['status'] == 'canceled') {
                        $sErrorIdent = 'PAYMENTAG_ERROR_ORDER_CANCELED';
                    } elseif($aResult['status'] == 'failed') {
                        $status = Vars::PAYMENT_STATUS_FAILED;
                        $sErrorIdent = 'PAYMENTAG_ERROR_ORDER_FAILED';
                    }

                    $order->oxorder__cdpaymentstatus = new Field($status);
                    $order->save();

                    // TODO OBT ERROR

                    throw new PaymentagPaymentException(Translator::getTranslatedString($sErrorIdent));
                } else {
                    // TODO: TEST recreate basket
                    $order->paymentagPrepareFinalizeOrder();
                    $bReturn = null;

                    try {
                        $oBasket = $this->getBasket();
                        $oUser = $this->getUser();

                        $iSuccess = $order->finalizeOrder($oBasket, $oUser);

                        $oUser->onOrderExecute($oBasket, $iSuccess);

                        $bReturn = $this->_getNextStep($iSuccess);
                    } catch(\OxidEsales\Eshop\Core\Exception\OutOfStockException $oEx) {
                        $oEx->setDestination('basket');
                        Session::addErrorToDisplay($oEx, false, true, 'basket');
                    } catch(\OxidEsales\Eshop\Core\Exception\NoArticleException|\OxidEsales\Eshop\Core\Exception\ArticleInputException $oEx) {
                        Session::addErrorToDisplay($oEx);
                    }

                    if($bReturn) {
                        if($order->oxorder__cdpaymentstatus->value === Vars::PAYMENT_STATUS_PENDING) {
                            $order->oxorder__oxtransstatus->setValue(Vars::TRANSACTION_STATUS_PENDING);
                            $order->save();
                        }
                    }

                    return $bReturn;
                }
            }
        } catch(\Exception $ex) {
            if($order) {
                $order->oxorder__oxtransstatus = new Field(Vars::TRANSACTION_STATUS_FAILED);
                $order->oxorder__oxfolder = new Field('ORDERFOLDER_PROBLEMS');

                $cancelMode = Config::getCancelMode();

                if(!empty($cancelMode) && $cancelMode === 'delete') {
                    $order->delete();
                } else {
                    $order->cancelOrder();
                }

                Session::deleteSessionChallenge();

                if($order->oxorder__cdpaymentstatus->value === Vars::PAYMENT_STATUS_FAILED) {
                    Session::setPayError(2);
                    $sPaymentUrl = Request::getShopUrl() . 'index.php?cl=payment&payerror=2';

                    // TODO INVOICE ERROR
                } else {
                    $sPaymentUrl = Request::getShopUrl() . 'index.php?cl=payment';
                }

                Request::doRedirect($sPaymentUrl);
            }
        }

        return false;
    }

    /**
     * @param $request
     * @return ApiUrlValidatorService|null
     */
    private function isRequestValid($request): ?ApiUrlValidatorService {
        if(!isset($request['clientid'])) {
            Session::addErrorToDisplay(new \Exception("Invalid Client ID"));

            return null;
        }

        $clientID = Config::getClientId();
        $requestClientID = $request['clientid'];

        if($requestClientID != $clientID) {
            Session::addErrorToDisplay(new \Exception("Invalid Client ID"));

            return null;
        }

        if(!empty($request['errortext'])) {
            $response = new ApiFailedUrlValidator();
        } else {
            $response = new ApiSuccessfulUrlValidator();
        }

        foreach($request as $key => $value) {
            $response->set($key, $value);
        }

        if(empty($request['errortext'])) {
            $hash = $response->getHash();

            if(strtolower($hash) != strtolower($request['hash'])) {
                #var_dump($hash, $request["hash"]);
                #return null;
            }
        }

        return $response;
    }

    private function handleRequestValues(ApiUrlValidatorService $request, OrderExtension $order) {
        $transactionStatus = $request->get("transactionstatus");
        $internal_order_id = $request->get("referenceid");
        $providerpurpose = $request->get("providerpurpose");
        $referenceid = $request->get("referenceid");
        $transactionid = $request->get("transactionid");
        $amount = $request->get("amount");
        $errornumber = $request->get("errornumber");
        $errortext = $request->get("errortext");

        $order->oxorder__providerpurpose = new Field($providerpurpose);

        if(!empty($errornumber) || !empty($errortext)) {
            $order->oxorder__providerpurpose = new Field($errortext);
            $order->cancelOrder();
            return ['success' => false, 'status' => 'failed', 'errorId' => $errornumber, 'error' => $errortext];
        }

        // Check transaction status
        switch($transactionStatus) {
            case 'Reserved':
            case 'Charged':
                break;

            default:
                $order->cancelOrder();
                return ['success' => false, 'status' => $transactionStatus, 'error' => 'Unsupported transaction status'];
        }

        // Order is canceled? Revert....
        if($order->oxorder__oxstorno->value == 1) {
            $order->oxorder__oxstorno = new \OxidEsales\Eshop\Core\Field(0);
            if($order->save()) {
                // canceling ordered products
                foreach($order->getOrderArticles() as $oOrderArticle) {
                    if($oOrderArticle->oxorderarticles__oxstorno->value == 1) {
                        $oOrderArticle->oxorderarticles__oxstorno = new \OxidEsales\Eshop\Core\Field(0);
                        if($oOrderArticle->save()) {
                            $oOrderArticle->updateArticleStock($oOrderArticle->oxorderarticles__oxamount->value * -1, false);
                        }
                    }
                }
            }
        }

        switch($transactionStatus) {
            case 'Reserved':
                $order->oxorder__oxtransid = new Field("{$referenceid}:{$transactionid}");
                $order->oxorder__cdpaymentstatus = new Field(Vars::PAYMENT_STATUS_PENDING);
                break;

            case 'Charged':
                $paidAmount = intval($amount) / 100;

                if(abs($paidAmount - $order->oxorder__oxtotalordersum->value) < 0.01) {
                    $order->oxorder__oxpaid = new Field (date("Y-m-d H:i:s"));
                    $order->oxorder__cdpaymentstatus = new Field(Vars::PAYMENT_STATUS_OK);
                } else {
                    $order->oxorder__cdpaymentstatus = new Field(Vars::PAYMENT_STATUS_PENDING);
                }

                break;
        }

        $order->save();

        return ['success' => true, 'status' => $transactionStatus];
    }

}
