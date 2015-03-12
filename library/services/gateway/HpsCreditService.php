<?php

class HpsCreditService extends HpsGatewayService{

    public function __construct(HpsServicesConfig $config = null){
        parent::__construct($config);
    }

    public function get($transactionId){
        if($transactionId <= 0 || $transactionId == null){
            throw new HpsArgumentException('Invalid Transaction Id');
        }

        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
            $hpsReportTxnDetail = $xml->createElement('hps:ReportTxnDetail');
            $hpsReportTxnDetail->appendChild($xml->createElement('hps:TxnId',$transactionId));
        $hpsTransaction->appendChild($hpsReportTxnDetail);

        return $this->_submitTransaction($hpsTransaction,'ReportTxnDetail');
    }

    public function listTransactions($startDate, $endDate, $filterBy=null){
        $this->_filterBy = $filterBy;
        date_default_timezone_set("UTC");
        $dateFormat = 'Y-m-d\TH:i:s.00\Z';
        $current = new DateTime();
        $currentTime = $current->format($dateFormat);

        if($startDate > $currentTime){
            throw HpsExceptionMapper::map_sdk_exception(HpsSdkCodes::$invalidStartDate);
        }
        else if($endDate > $currentTime){
            throw HpsExceptionMapper::map_sdk_exception(HpsSdkCodes::$invalidEndDate);
        }

        HpsInputValidation::checkDateNotFuture($startDate);
        HpsInputValidation::checkDateNotFuture($endDate);

        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
            $hpsReportActivity = $xml->createElement('hps:ReportActivity');
                $hpsReportActivity->appendChild($xml->createElement('hps:RptStartUtcDT',$startDate));
                $hpsReportActivity->appendChild($xml->createElement('hps:RptEndUtcDT',$endDate));
        $hpsTransaction->appendChild($hpsReportActivity);

        return $this->_submitTransaction($hpsTransaction, 'ReportActivity');
    }

    public function charge($amount, $currency, $cardOrToken, $cardHolder=null, $requestMultiUseToken=false, $details=null, $txnDescriptor=null, $allowPartialAuth=false){
        HpsInputValidation::checkCurrency($currency);
        $this->_currency = $currency;
        $this->_amount = HpsInputValidation::checkAmount($amount);

        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
            $hpsCreditSale = $xml->createElement('hps:CreditSale');
                $hpsBlock1 = $xml->createElement('hps:Block1');
                    $hpsBlock1->appendChild($xml->createElement('hps:AllowDup','Y'));
                    if($allowPartialAuth){
                        $hpsBlock1->appendChild($xml->createElement('hps:AllowPartialAuth','Y'));
                    }else{
                        $hpsBlock1->appendChild($xml->createElement('hps:AllowPartialAuth','N'));
                    }
                    $hpsBlock1->appendChild($xml->createElement('hps:Amt',$amount));
                    if($cardHolder != null){
                        $hpsBlock1->appendChild($this->_hydrateCardHolderData($cardHolder,$xml));
                    }

                    if($details != null){
                        $hpsBlock1->appendChild($this->_hydrateAdditionalTxnFields($details,$xml));
                    }
                    if($txnDescriptor != null && $txnDescriptor != ''){
                        $hpsBlock1->appendChild($xml->createElement('hps:TxnDescriptor',$txnDescriptor));
                    }
                    $cardData = $xml->createElement('hps:CardData');
                    if($cardOrToken instanceOf HpsCreditCard){
                        $cardData->appendChild($this->_hydrateManualEntry($cardOrToken,$xml));
                    }else{
                        $tokenData = $xml->createElement('hps:TokenData');
                        $tokenData->appendChild($xml->createElement('hps:TokenValue',$cardOrToken->tokenValue));
                        $cardData->appendChild($tokenData);
                    }
                    $cardData->appendChild($xml->createElement('hps:TokenRequest',($requestMultiUseToken) ? 'Y' : 'N'));
                $hpsBlock1->appendChild($cardData);
            $hpsCreditSale->appendChild($hpsBlock1);
        $hpsTransaction->appendChild($hpsCreditSale);

        return $this->_submitTransaction($hpsTransaction,'CreditSale',(isset($details->clientTxnId) ? $details->clientTxnId : null), $cardOrToken);
    }

    public function verify($cardOrToken, $cardHolder=null, $requestMultiUseToken=false,$clientTransactionId=null){
        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
            $hpsCreditAccountVerify = $xml->createElement('hps:CreditAccountVerify');
                $hpsBlock1 = $xml->createElement('hps:Block1');
                    if($cardHolder != null){
                        $hpsBlock1->appendChild($this->_hydrateCardHolderData($cardHolder,$xml));
                    }
                    $cardData = $xml->createElement('hps:CardData');
                    if($cardOrToken instanceOf HpsCreditCard){
                        $cardData->appendChild($this->_hydrateManualEntry($cardOrToken,$xml));
                    }else{
                        $tokenData = $xml->createElement('hps:TokenData');
                        $tokenData->appendChild($xml->createElement('hps:TokenValue',$cardOrToken->tokenValue));
                        $cardData->appendChild($tokenData);
                    }
                    $cardData->appendChild($xml->createElement('hps:TokenRequest',($requestMultiUseToken) ? 'Y' : 'N'));
                $hpsBlock1->appendChild($cardData);
            $hpsCreditAccountVerify->appendChild($hpsBlock1);
        $hpsTransaction->appendChild($hpsCreditAccountVerify);
        return $this->_submitTransaction($hpsTransaction,'CreditAccountVerify',$clientTransactionId);

    }

    public function authorize($amount, $currency, $cardOrToken, $cardHolder=null, $requestMultiUseToken=false, $details=null, $txnDescriptor=null, $allowPartialAuth=false){
        HpsInputValidation::checkCurrency($currency);
        $this->_currency = $currency;
        $this->_amount = HpsInputValidation::checkAmount($amount);

        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
            $hpsCreditAuth = $xml->createElement('hps:CreditAuth');
                $hpsBlock1 = $xml->createElement('hps:Block1');
                    $hpsBlock1->appendChild($xml->createElement('hps:AllowDup','Y'));
                    if($allowPartialAuth){
                        $hpsBlock1->appendChild($xml->createElement('hps:AllowPartialAuth','Y'));
                    }else{
                        $hpsBlock1->appendChild($xml->createElement('hps:AllowPartialAuth','N'));
                    }
                    $hpsBlock1->appendChild($xml->createElement('hps:Amt',$amount));
                    if($cardHolder != null){
                        $hpsBlock1->appendChild($this->_hydrateCardHolderData($cardHolder,$xml));
                    }
                    if($details != null){
                        $hpsBlock1->appendChild($this->_hydrateAdditionalTxnFields($details,$xml));
                    }
                    if($txnDescriptor != null && $txnDescriptor != ''){
                        $hpsBlock1->appendChild($xml->createElement('hps:TxnDescriptor',$txnDescriptor));
                    }
                    $cardData = $xml->createElement('hps:CardData');
                    if($cardOrToken instanceOf HpsCreditCard){
                        $cardData->appendChild($this->_hydrateManualEntry($cardOrToken,$xml));
                    }else{
                        $tokenData = $xml->createElement('hps:TokenData');
                        $tokenData->appendChild($xml->createElement('hps:TokenValue',$cardOrToken->tokenValue));
                        $cardData->appendChild($tokenData);
                    }
                    $cardData->appendChild($xml->createElement('hps:TokenRequest',($requestMultiUseToken) ? 'Y' : 'N'));
                $hpsBlock1->appendChild($cardData);
            $hpsCreditAuth->appendChild($hpsBlock1);
        $hpsTransaction->appendChild($hpsCreditAuth);
        return $this->_submitTransaction($hpsTransaction, 'CreditAuth', (isset($details->clientTxnId) ? $details->clientTxnId : null), $cardOrToken);
    }

    public function capture($transactionId, $amount=null, $gratuity=null, $clientTransactionId=null){
        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
            $hpsCreditAddToBatch = $xml->createElement('hps:CreditAddToBatch');
                $hpsCreditAddToBatch->appendChild($xml->createElement('hps:GatewayTxnId',$transactionId));
                if($amount != null){
                    $amount = sprintf("%0.2f",round($amount,3));
                    $hpsCreditAddToBatch->appendChild($xml->createElement('hps:Amt',$amount));
                }
                if($gratuity != null){
                    $hpsCreditAddToBatch->appendChild($xml->createElement('hps:GratuityAmtInfo',$gratuity));
                }
        $hpsTransaction->appendChild($hpsCreditAddToBatch);
        $response = $this->doTransaction($hpsTransaction,$clientTransactionId);
        $this->_processChargeGatewayResponse($response,'CreditAddToBatch');

        return $this->get($transactionId);
    }

    public function refund($amount, $currency, $cardData, $details=null){
        HpsInputValidation::checkCurrency($currency);
        $this->_currency = $currency;
        $this->_amount = HpsInputValidation::checkAmount($amount);

        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
            $hpsCreditReturn = $xml->createElement('hps:CreditReturn');
                $hpsBlock1 = $xml->createElement('hps:Block1');
                    $hpsBlock1->appendChild($xml->createElement('hps:AllowDup','Y'));
                    $hpsBlock1->appendChild($xml->createElement('hps:Amt',$amount));

                    if($cardData instanceOf HpsCreditCard){
                        $cardDataElement = $xml->createElement('hps:CardData');
                        $cardDataElement->appendChild($this->_hydrateManualEntry($cardData,$xml));
                        $hpsBlock1->appendChild($cardDataElement);
                    }else if($cardData instanceof HpsTokenData){
                        $cardDataElement = $xml->createElement('hps:CardData');
                        $tokenData = $xml->createElement('hps:TokenData');
                        $tokenData->appendChild($xml->createElement('hps:TokenValue',$cardData->tokenValue));
                        $cardDataElement->appendChild($tokenData);
                        $hpsBlock1->appendChild($cardDataElement);
                    }else{
                        $hpsBlock1->appendChild($xml->createElement('hps:GatewayTxnId',$cardData));
                    }
                    if($details != null){
                        $hpsBlock1->appendChild($this->_hydrateAdditionalTxnFields($details,$xml));
                    }
            $hpsCreditReturn->appendChild($hpsBlock1);
        $hpsTransaction->appendChild($hpsCreditReturn);
        return $this->_submitTransaction($hpsTransaction,'CreditReturn',(isset($details->clientTxnId) ? $details->clientTxnId : null));
    }

    public function reverse($cardData, $amount, $currency, $details=null){
        HpsInputValidation::checkCurrency($currency);
        $this->_currency = $currency;
        $this->_amount = HpsInputValidation::checkAmount($amount);

        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
            $hpsCreditReversal = $xml->createElement('hps:CreditReversal');
                $hpsBlock1 = $xml->createElement('hps:Block1');
                    $hpsBlock1->appendChild($xml->createElement('hps:Amt',$amount));
                    if($details != null){
                        $hpsBlock1->appendChild($this->_hydrateAdditionalTxnFields($details,$xml));
                    }
                    if($cardData instanceOf HpsCreditCard){
                        $cardDataElement = $xml->createElement('hps:CardData');
                        $cardDataElement->appendChild($this->_hydrateManualEntry($cardData,$xml));
                        $hpsBlock1->appendChild($cardDataElement);
                    }else if($cardData instanceof HpsTokenData){
                        $cardDataElement = $xml->createElement('hps:CardData');
                        $tokenData = $xml->createElement('hps:TokenData');
                        $tokenData->appendChild($xml->createElement('hps:TokenValue',$cardData->tokenValue));
                        $cardDataElement->appendChild($tokenData);
                        $hpsBlock1->appendChild($cardDataElement);
                    }else{
                        $hpsBlock1->appendChild($xml->createElement('hps:GatewayTxnId',$cardData));
                    }
                    if($details != null){
                        $hpsBlock1->appendChild($this->_hydrateAdditionalTxnFields($details,$xml));
                    }
            $hpsCreditReversal->appendChild($hpsBlock1);
        $hpsTransaction->appendChild($hpsCreditReversal);

        return $this->_submitTransaction($hpsTransaction,'CreditReversal',(isset($details->clientTxnId) ? $details->clientTxnId : null));
    }

    public function void($transactionId,$clientTransactionId=null){
        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
            $hpsCreditVoid = $xml->createElement('hps:CreditVoid');
            $hpsCreditVoid->appendChild($xml->createElement('hps:GatewayTxnId',$transactionId));
        $hpsTransaction->appendChild($hpsCreditVoid);

        return $this->_submitTransaction($hpsTransaction,'CreditVoid',$clientTransactionId);
    }

    public function edit($transactionId, $amount=null, $gratuity=null, $clientTransactionId=null){
        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
            $hpsCreditTxnEdit = $xml->createElement('hps:CreditTxnEdit');
            $hpsCreditTxnEdit->appendChild($xml->createElement('hps:GatewayTxnId',$transactionId));
            if($amount != null){
                $amount = sprintf("%0.2f",round($amount,3));
                $hpsCreditTxnEdit->appendChild($xml->createElement('hps:Amt',$amount));
            }
            if($gratuity != null){
                $hpsCreditTxnEdit->appendChild($xml->createElement('hps:GratuityAmtInfo',$gratuity));
            }
        $hpsTransaction->appendChild($hpsCreditTxnEdit);
        $trans = $this->_submitTransaction($hpsTransaction,'CreditTxnEdit',$clientTransactionId);
        $trans->responseCode = '00';
        $trans->responseText = '';

        return $trans;
    }

    private function _hydrateAdditionalTxnFields($details,DOMDocument $xml){
        $additionalTxnFields = $xml->createElement('hps:AdditionalTxnFields');
        if(isset($details->memo) && $details->memo != null && $details->memo != ""){
            $additionalTxnFields->appendChild($xml->createElement('hps:Description',$details->memo));
        }
        if(isset($details->invoiceNumber) && $details->invoiceNumber != null && $details->invoiceNumber != ""){
            $additionalTxnFields->appendChild($xml->createElement('hps:InvoiceNbr',$details->invoiceNumber));
        }
        if(isset($details->customerId) && $details->customerId != null && $details->customerId != ""){
            $additionalTxnFields->appendChild($xml->createElement('hps:CustomerID',$details->customerId));
        }
        return $additionalTxnFields;
    }

    private function _hydrateCardHolderData(HpsCardHolder $cardHolder, DOMDocument $xml){
        $cardHolderData = $xml->createElement('hps:CardHolderData');
        $cardHolderData->appendChild($xml->createElement('hps:CardHolderFirstName',$cardHolder->firstName));
        $cardHolderData->appendChild($xml->createElement('hps:CardHolderLastName',$cardHolder->lastName));
        $cardHolderData->appendChild($xml->createElement('hps:CardHolderEmail',$cardHolder->email));
        $cardHolderData->appendChild($xml->createElement('hps:CardHolderPhone',$cardHolder->phone));
        $cardHolderData->appendChild($xml->createElement('hps:CardHolderAddr',$cardHolder->address->address));
        $cardHolderData->appendChild($xml->createElement('hps:CardHolderCity',$cardHolder->address->city));
        $cardHolderData->appendChild($xml->createElement('hps:CardHolderState',$cardHolder->address->state));
        $cardHolderData->appendChild($xml->createElement('hps:CardHolderZip',$cardHolder->address->zip));

        return $cardHolderData;
    }

    private function _hydrateManualEntry(HpsCreditCard $card,DOMDocument $xml){
        $manualEntry = $xml->createElement('hps:ManualEntry');
        if($card->number != null && $card->number != ""){
            $manualEntry->appendChild($xml->createElement('hps:CardNbr',$card->number));
        }
        if($card->expMonth != null && $card->expMonth != ""){
            $manualEntry->appendChild($xml->createElement('hps:ExpMonth',$card->expMonth));
        }
        if($card->expYear != null && $card->expYear != ""){
            $manualEntry->appendChild($xml->createElement('hps:ExpYear',$card->expYear));
        }
        if($card->cvv != null && $card->cvv != ""){
            $manualEntry->appendChild($xml->createElement('hps:CVV2',$card->cvv));
        }
        $manualEntry->appendChild($xml->createElement('hps:CardPresent',"N"));
        $manualEntry->appendChild($xml->createElement('hps:ReaderPresent',"N"));

        return $manualEntry;
    }

    private function _submitTransaction($transaction, $txnType, $clientTxnId=null, $cardData = null){
        try{
        	
            $response = $this->doTransaction($transaction,$clientTxnId);
        }catch (HpsException $e){

            if($e->innerException != null && $e->innerException->getMessage() == "gateway_time-out"){
                if($txnType == 'CreditSale' || $txnType == 'CreditAuth'){
                    try{
                        $this->reverse($cardData, $this->_amount, $this->_currency);
                    } catch(Exception $e){
                        throw new HpsGatewayException('0',HpsExceptionCodes::GatewayTimeoutReversalError);
                    }
                }
                throw new HpsException("An error occurred and the gateway has timed out",'gateway_timeout',$e,'gateway_timeout');
            }
            throw $e;
        }

        $this->_processChargeGatewayResponse($response,$txnType);
        $this->_processChargeIssuerResponse($response,$txnType);

        $rvalue = null;
        if($txnType == 'ReportTxnDetail'){ $rvalue = HpsReportTransactionDetails::fromDict($response,$txnType);}
        elseif($txnType == 'ReportActivity'){ $rvalue = HpsReportTransactionSummary::fromDict($response,$txnType,$this->_filterBy);}
        elseif($txnType == 'CreditSale'){ $rvalue = HpsCharge::fromDict($response,$txnType);}
        elseif($txnType == 'CreditAccountVerify'){ $rvalue = HpsAccountVerify::fromDict($response,$txnType);}
        elseif($txnType == 'CreditAuth'){ $rvalue = HpsAuthorization::fromDict($response,$txnType);}
        elseif($txnType == 'CreditReturn'){ $rvalue = HpsRefund::fromDict($response,$txnType);}
        elseif($txnType == 'CreditReversal'){ $rvalue = HpsReversal::fromDict($response,$txnType);}
        elseif($txnType == 'CreditVoid'){ $rvalue = HpsVoid::fromDict($response,$txnType);}

        return $rvalue;
    }

    private function _processChargeGatewayResponse($response, $expectedType){
        $gatewayRspCode = (isset($response->Header->GatewayRspCode) ? $response->Header->GatewayRspCode : null);
        $transactionId = (isset($response->Header->GatewayTxnId) ? $response->Header->GatewayTxnId : null);

        if($gatewayRspCode == '0'){
            return;
        }

        if($gatewayRspCode == '30'){
            try{
                $this->reverse($transactionId, $this->_amount, $this->_currency);
            } catch(Exception $e){
                throw new HpsGatewayException(HpsExceptionCodes::GatewayTimeoutReversalError,
                    'Error occurred while reversing a charge due to Hps gateway time-out.',$e);
            }
        }
        HpsGatewayResponseValidation::checkResponse($response,$expectedType);
    }

    private function _processChargeIssuerResponse($response,$expectedType){
        $transactionId = (isset($response->Header->GatewayTxnId) ? $response->Header->GatewayTxnId : null);
        $item = $response->Transaction->$expectedType;

        if($item != null){
            $responseCode = (isset($item->RspCode) ? $item->RspCode : null);
            $responseText = (isset($item->RspText) ? $item->RspText : null);

            if( $responseCode != null){
                // check if we need to do a reversal
                if( $responseCode == '91'){
                    try{
                        $this->reverse($transactionId, $this->_amount, $this->_currency);
                    }catch (HpsGatewayException $e){
                        // if the transaction wasn't found; throw the original timeout exception.
                        if($e->details->gatewayResponseCode == 3){
                            HpsIssuerResponseValidation::checkResponse($transactionId,$responseCode,$responseText);
                        }
                        throw new HpsCreditException($transactionId,HpsExceptionCodes::IssuerTimeoutReversalError,
                            'Error occurred while reversing a charge due to HPS issuer time-out',$e);
                    }catch(HpsException $e){
                        throw new HpsCreditException($transactionId,HpsExceptionCodes::IssuerTimeoutReversalError,
                            'Error occurred while reversing a charge due to HPS issuer time-out',$e);
                    }
                }
             HpsIssuerResponseValidation::checkResponse($transactionId,$responseCode,$responseText);
			
            }
        }
    }
} 