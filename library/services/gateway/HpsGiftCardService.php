<?php

class HpsGiftCardService extends HpsGatewayService{

    public function __construct(HpsServicesConfig $config){
        parent::__construct($config);
    }

    public function activate($amount, $currency, $giftCard){
        HpsInputValidation::checkCurrency($currency);
        $amount = HpsInputValidation::checkAmount($amount);
        $txnType = 'GiftCardActivate';

        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
            $hpsGiftCard = $xml->createElement('hps:'.$txnType);
                $hpsBlock1 = $xml->createElement('hps:Block1');
                $hpsBlock1->appendChild($xml->createElement('hps:Amt',$amount));
                $hpsBlock1->appendChild($this->_hydrateGiftCardData($giftCard,$xml));
            $hpsGiftCard->appendChild($hpsBlock1);
        $hpsTransaction->appendChild($hpsGiftCard);

        return $this->_submitTransaction($hpsTransaction,$txnType);
    }

    public function addValue($amount, $currency, $giftCard){
        HpsInputValidation::checkCurrency($currency);
        $amount = HpsInputValidation::checkAmount($amount);
        $txnType = 'GiftCardAddValue';

        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
            $hpsGiftCard = $xml->createElement('hps:'.$txnType);
                $hpsBlock1 = $xml->createElement('hps:Block1');
                $hpsBlock1->appendChild($xml->createElement('hps:Amt',$amount));
                $hpsBlock1->appendChild($this->_hydrateGiftCardData($giftCard,$xml));
            $hpsGiftCard->appendChild($hpsBlock1);
        $hpsTransaction->appendChild($hpsGiftCard);

        return $this->_submitTransaction($hpsTransaction,$txnType);
    }

    public function alias($action, $giftCard, $aliasStr){
        $txnType = 'GiftCardAlias';

        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
            $hpsGiftCardAlias = $xml->createElement('hps:'. $txnType);
                $hpsBlock1 = $xml->createElement('hps:Block1');
                $hpsBlock1->appendChild($xml->createElement('hps:Action',strtoupper($action)));
                $hpsBlock1->appendChild($xml->createElement('hps:Alias',$aliasStr));
                $hpsBlock1->appendChild($this->_hydrateGiftCardData($giftCard,$xml));
            $hpsGiftCardAlias->appendChild($hpsBlock1);
        $hpsTransaction->appendChild($hpsGiftCardAlias);

        return $this->_submitTransaction($hpsTransaction,$txnType);
    }

    public function balance($giftCard){
        $txnType = 'GiftCardBalance';

        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
            $hpsGiftCard = $xml->createElement('hps:'. $txnType);
                $hpsBlock1 = $xml->createElement('hps:Block1');
                $hpsBlock1->appendChild($this->_hydrateGiftCardData($giftCard,$xml));
            $hpsGiftCard->appendChild($hpsBlock1);
        $hpsTransaction->appendChild($hpsGiftCard);

        return $this->_submitTransaction($hpsTransaction,$txnType);
    }

    public function deactivate($giftCard){
        $txnType = 'GiftCardDeactivate';

        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
            $hpsGiftCard = $xml->createElement('hps:'. $txnType);
                $hpsBlock1 = $xml->createElement('hps:Block1');
                $hpsBlock1->appendChild($this->_hydrateGiftCardData($giftCard,$xml));
            $hpsGiftCard->appendChild($hpsBlock1);
        $hpsTransaction->appendChild($hpsGiftCard);

        return $this->_submitTransaction($hpsTransaction,$txnType);
    }

    public function replace($oldGiftCard, $newGiftCard){
        $txnType = 'GiftCardReplace';

        $xml = new DOMDocument();
            $hpsTransaction = $xml->createElement('hps:Transaction');
            $hpsGiftCard = $xml->createElement('hps:'. $txnType);
                $hpsBlock1 = $xml->createElement('hps:Block1');
                $hpsBlock1->appendChild($this->_hydrateGiftCardData($oldGiftCard,$xml,'OldCardData'));
                $hpsBlock1->appendChild($this->_hydrateGiftCardData($newGiftCard,$xml,'NewCardData'));
            $hpsGiftCard->appendChild($hpsBlock1);
        $hpsTransaction->appendChild($hpsGiftCard);

        return $this->_submitTransaction($hpsTransaction,$txnType);
    }

    public function reverse($cardData, $amount, $currency='usd'){
        $txnType = 'GiftCardReversal';

        HpsInputValidation::checkCurrency($currency);
        $amount = HpsInputValidation::checkAmount($amount);

        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
            $hpsGiftCard = $xml->createElement('hps:'. $txnType);
                $hpsBlock1 = $xml->createElement('hps:Block1');
                $hpsBlock1->appendChild($xml->createElement('hps:Amt',$amount));
                if($cardData instanceOf HpsGiftCard){
                    $hpsBlock1->appendChild($this->_hydrateGiftCardData($cardData,$xml));
                }else{
                    $hpsBlock1->appendChild($xml->createElement('hps:GatewayTxnId',$cardData));
                }
            $hpsGiftCard->appendChild($hpsBlock1);
        $hpsTransaction->appendChild($hpsGiftCard);

        return $this->_submitTransaction($hpsTransaction,$txnType);
    }

    public function reward($giftCard, $amount, $currency='usd', $gratuity=null, $tax=null){
        $txnType = 'GiftCardReward';

        HpsInputValidation::checkCurrency($currency);
        $amount = HpsInputValidation::checkAmount($amount);

        $xml = new DOMDocument();
            $hpsTransaction = $xml->createElement('hps:Transaction');
                $hpsGiftCard = $xml->createElement('hps:'. $txnType);
                    $hpsBlock1 = $xml->createElement('hps:Block1');
                    $hpsBlock1->appendChild($xml->createElement('hps:Amt',$amount));
                    $hpsBlock1->appendChild($this->_hydrateGiftCardData($giftCard,$xml));
                    if(strtolower($currency) == 'usd' || $currency == 'points'){
                        $hpsBlock1->appendChild($xml->createElement('hps:Currency',(strtolower($currency) == 'usd' ? 'USD' : 'POINTS')));
                    }
                    if($gratuity != null){
                        $hpsBlock1->appendChild($xml->createElement('hps:GratuityAmtInfo',$gratuity));
                    }
                    if($tax != null){
                        $hpsBlock1->appendChild($xml->createElement('hps:TaxAmtInfo', $tax));
                    }
            $hpsGiftCard->appendChild($hpsBlock1);
        $hpsTransaction->appendChild($hpsGiftCard);

        return $this->_submitTransaction($hpsTransaction,$txnType);
    }

    public function sale($giftCard, $amount, $currency='usd', $gratuity=null, $tax=null){
        $txnType = 'GiftCardSale';

        HpsInputValidation::checkCurrency($currency);
        $amount = HpsInputValidation::checkAmount($amount);

        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
            $hpsGiftCard = $xml->createElement('hps:'. $txnType);
                $hpsBlock1 = $xml->createElement('hps:Block1');
                    $hpsBlock1->appendChild($xml->createElement('hps:Amt',$amount));
                    $hpsBlock1->appendChild($this->_hydrateGiftCardData($giftCard,$xml));
                    if(strtolower($currency) == 'usd' || $currency == 'points'){
                        $hpsBlock1->appendChild($xml->createElement('hps:Currency',(strtolower($currency) == 'usd' ? 'USD' : 'POINTS')));
                    }
                    if($gratuity != null){
                        $hpsBlock1->appendChild($xml->createElement('hps:GratuityAmtInfo',$gratuity));
                    }
                    if($tax != null){
                        $hpsBlock1->appendChild($xml->createElement('hps:TaxAmtInfo', $tax));
                    }
            $hpsGiftCard->appendChild($hpsBlock1);
        $hpsTransaction->appendChild($hpsGiftCard);

        return $this->_submitTransaction($hpsTransaction,$txnType);
    }

    public function void($transactionId){
        $txnType = 'GiftCardVoid';

        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
            $hpsGiftCard = $xml->createElement('hps:'. $txnType);
                $hpsBlock1 = $xml->createElement('hps:Block1');
                $hpsBlock1->appendChild($xml->createElement('hps:GatewayTxnId',$transactionId));
            $hpsGiftCard->appendChild($hpsBlock1);
        $hpsTransaction->appendChild($hpsGiftCard);

        return $this->_submitTransaction($hpsTransaction,$txnType);
    }

    private function _hydrateGiftCardData(HpsGiftCard $giftCard, DOMDocument $xml, $elementName='CardData'){
        $giftCardData = $xml->createElement('hps:'.$elementName);
        if($giftCard->isTrackData == true){
            $giftCardData->appendChild($xml->createElement('hps:TrackData',$giftCard->number));
        }else{
            $giftCardData->appendChild($xml->createElement('hps:CardNbr',$giftCard->number));
        }

        if($giftCard->encryptionData != null){
            $encData = $xml->createElement('hps:EncryptionData');
            $encData->appendChild($xml->createElement('hps:EncryptedTrackNumber',$giftCard->encryptionData->encryptedTrackNumber));
            $encData->appendChild($xml->createElement('hps:KSN',$giftCard->encryptionData->ksn));
            $encData->appendChild($xml->createElement('hps:KTB',$giftCard->encryptionData->ksn));
            $encData->appendChild($xml->createElement('hps:Version',$giftCard->encryptionData->version));
            $giftCardData->appendChild($encData);
        }

        return $giftCardData;
    }

    private function _submitTransaction($transaction,$txnType,$clientTxnId=null){
        $response = $this->doTransaction($transaction,$clientTxnId);

        HpsGatewayResponseValidation::checkResponse($response,$txnType);
        HpsIssuerResponseValidation::checkResponse($response->Header->GatewayTxnId,
            $response->Transaction->$txnType->RspCode,$response->Transaction->$txnType->RspText);

        $rvalue = '';

        if($txnType == 'GiftCardActivate'){ $rvalue = HpsGiftCardActivate::fromDict($response,$txnType,'HpsGiftCardActivate');}
        else if($txnType == 'GiftCardAddValue'){$rvalue = HpsGiftCardAddValue::fromDict($response,$txnType,'HpsGiftCardAddValue');}
        else if($txnType == 'GiftCardAlias'){$rvalue = HpsGiftCardAlias::fromDict($response,$txnType,'HpsGiftCardAlias');}
        else if($txnType == 'GiftCardBalance'){$rvalue = HpsGiftCardBalance::fromDict($response,$txnType,'HpsGiftCardBalance');}
        else if($txnType == 'GiftCardDeactivate'){$rvalue = HpsGiftCardDeactivate::fromDict($response,$txnType,'HpsGiftCardDeactivate');}
        else if($txnType == 'GiftCardReplace'){$rvalue = HpsGiftCardReplace::fromDict($response,$txnType,'HpsGiftCardReplace');}
        else if($txnType == 'GiftCardReward'){$rvalue = HpsGiftCardReward::fromDict($response,$txnType,'HpsGiftCardReward');}
        else if($txnType == 'GiftCardSale'){$rvalue = HpsGiftCardSale::fromDict($response,$txnType,'HpsGiftCardSale');}
        else if($txnType == 'GiftCardVoid'){$rvalue = HpsGiftCardVoid::fromDict($response,$txnType,'HpsGiftCardVoid');}
        else if($txnType == 'GiftCardReversal'){$rvalue = HpsGiftCardReversal::fromDict($response,$txnType,'HpsGiftCardReversal');}

        return $rvalue;
    }
} 