<?php

class HpsReportTransactionSummary extends HpsTransaction{
    public  $amount                 = null,
            $settlementAmount       = null,
            $originalTransactionId  = null,
            $maskedCardNumber       = null,
            $transactionType        = null,
            $transactionUTCDate     = null,
            $exceptions             = null;

    public static function fromDict($rsp,$txnType,$filterBy =null,$returnType = 'HpsReportTransactionSummary'){
        $transactions = array();

        if($rsp->Transaction->ReportActivity->Header->TxnCnt == "0"){
            return $transactions;
        }

        $summary = null;
        $serviceName = (isset($filterBy) ? HpsTransaction::transactionTypeToServiceName($filterBy) : null);

        foreach ($rsp->Transaction->ReportActivity->Details as $charge) {
            if($filterBy == null || $charge->ServiceName != $serviceName){
                $summary = parent::fromDict($rsp,$txnType,$returnType);

                $summary->originalTransactionId = (isset($charge->OriginalGatewayTxnId) ? $charge->OriginalGatewayTxnId : null);
                $summary->maskedCardNumber = (isset($charge->MaskedCardNbr) ? $charge->MaskedCardNbr : null);
                $summary->responseCode = (isset($charge->IssuerRspCode) ? $charge->IssuerRspCode : null);
                $summary->responseText = (isset($charge->IssuerRspText) ? $charge->IssuerRspText : null);
                $summary->amount = (isset($charge->Amt) ? $charge->Amt : null);
                $summary->settlementAmount = (isset($charge->SettlementAmt) ? $charge->SettlementAmt : null);
                $summary->transactionType = (isset($charge->ServiceName) ? HpsTransaction::serviceNameToTransactionType($charge->ServiceName) : null);
                $summary->transactionUTCDate = (isset($charge->TxnUtcDT) ? $charge->TxnUtcDT : null );

                if($filterBy != null ){
                    $summary->transactionType = $filterBy;
                }

                $gwResponseCode = (isset($charge->GatewayRspCode) ? $charge->GatewayRspCode : null);
                $issuerResponseCode  = (isset($charge->IssuerRspCode) ? $charge->IssuerRspCode : null);

                if($gwResponseCode != "0" || $issuerResponseCode != "00"){
                    $exceptions = new HpsChargeExceptions();
                    if($gwResponseCode != "0"){
                        $message = $charge->GatewayRspMsg;
                        $exceptions->gatewayException = HpsGatewayResponseValidation::getException($charge->GatewayTxnId, $gwResponseCode, $message);
                    }
                    if($issuerResponseCode != "00"){
                        $message = $charge->IssuerRspText;
                        $exceptions->issuerException = HpsIssuerResponseValidation::getException($charge->GatewayTxnId, $issuerResponseCode, $message);
                    }
                    $summary->exceptions = $exceptions;
                }
            }
            $transactions[] = $summary;
        }
        return $transactions;
    }
} 