<?php

class HpsTransaction {
    public  $transactionId          = null,
            $clientTransactionId    = null,
            $responseCode           = null,
            $responseText           = null,
            $referenceNumber        = null;

    protected $_header                = null;

    static public function fromDict($rsp,$txnType,$returnType = null){

        $transaction = new $returnType();

        // Hydrate the header
        $transaction->_header = new HpsTransactionHeader();
        $transaction->_header->gatewayResponseCode = $rsp->Header->GatewayRspCode;
        $transaction->_header->gatewayResponseMessage = $rsp->Header->GatewayRspMsg;
        $transaction->_header->responseDt = $rsp->Header->RspDT;
        $transaction->_header->clientTxnId = (isset($rsp->Header->ClientTxnId) ? $rsp->Header->ClientTxnId : null);

        $transaction->transactionId = $rsp->Header->GatewayTxnId;
        if(isset($rsp->Header->ClientTxnIdSpecified) && $rsp->Header->ClientTxnIdSpecified == true){
            $transaction->clientTransactionId = $rsp->Header->clientTxnId;
        }

        // Hydrate the body
        $item = $rsp->Transaction->$txnType;
        if($item != null){
            $transaction->responseCode = (isset($item->RspCode) ? $item->RspCode : null);
            $transaction->responseText = (isset($item->RspText) ? $item->RspText : null);
            $transaction->referenceNumber = (isset($item->RefNbr) ? $item->RefNbr : null);
        }

        return $transaction;
    }

    static public function transactionTypeToServiceName($transactionType){
        switch ($transactionType){
            case HpsTransactionType::Authorize :
                return HpsItemChoiceTypePosResponseVer10Transaction::CreditAuth;
                break;

            case HpsTransactionType::Capture:
                return HpsItemChoiceTypePosResponseVer10Transaction::CreditAddToBatch;
                break;

            case HpsTransactionType::Charge:
                return HpsItemChoiceTypePosResponseVer10Transaction::CreditSale;
                break;

            case HpsTransactionType::Refund:
                return HpsItemChoiceTypePosResponseVer10Transaction::CreditReturn;
                break;

            case HpsTransactionType::Reverse:
                return HpsItemChoiceTypePosResponseVer10Transaction::CreditReversal;
                break;

            case HpsTransactionType::Verify:
                return HpsItemChoiceTypePosResponseVer10Transaction::CreditAccountVerify;
                break;

            case HpsTransactionType::ListTransaction:
                return HpsItemChoiceTypePosResponseVer10Transaction::ReportActivity;
                break;

            case HpsTransactionType::Get:
                return HpsItemChoiceTypePosResponseVer10Transaction::ReportTxnDetail;
                break;

            case HpsTransactionType::Void:
                return HpsItemChoiceTypePosResponseVer10Transaction::CreditVoid;
                break;

            case HpsTransactionType::BatchClose:
                return HpsItemChoiceTypePosResponseVer10Transaction::BatchClose;
                break;

            case HpsTransactionType::SecurityError:
                return "SecurityError";
                break;

            default:
                return "";
        }
    }

    static public function serviceNameToTransactionType($serviceName){
        switch ($serviceName){
            case HpsItemChoiceTypePosResponseVer10Transaction::CreditAuth:
                return HpsTransactionType::Capture;
                break;

            case HpsItemChoiceTypePosResponseVer10Transaction::CreditAddToBatch:
                return HpsTransactionType::Capture;
                break;

            case HpsItemChoiceTypePosResponseVer10Transaction::CreditSale:
                return HpsTransactionType::Charge;
                break;

            case HpsItemChoiceTypePosResponseVer10Transaction::CreditReturn:
                return HpsTransactionType::Refund;
                break;

            case HpsItemChoiceTypePosResponseVer10Transaction::CreditReversal:
                return HpsTransactionType::Reverse;
                break;

            case HpsItemChoiceTypePosResponseVer10Transaction::CreditAccountVerify:
                return HpsTransactionType::Verify;
                break;

            case HpsItemChoiceTypePosResponseVer10Transaction::ReportActivity:
                return HpsTransactionType::ListTransaction;
                break;

            case HpsItemChoiceTypePosResponseVer10Transaction::ReportTxnDetail:
                return HpsTransactionType::Get;
                break;

            case HpsItemChoiceTypePosResponseVer10Transaction::CreditVoid:
                return HpsTransactionType::Void;
                break;

            case HpsItemChoiceTypePosResponseVer10Transaction::BatchClose:
                return HpsTransactionType::BatchClose;
                break;

            default:
                return null;
        }
    }
}