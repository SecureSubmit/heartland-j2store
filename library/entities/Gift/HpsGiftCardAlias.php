<?php

class HpsGiftCardAlias extends HpsTransaction{
    /**
     * The HPS gift card alias response.
     */

    public $giftCard = null;

    static public function fromDict($rsp,$txnType,$returnType = null){
        $item = $rsp->Transaction->$txnType;

        $alis = new HpsGiftCardAlias();
        $alis->transactionId = $rsp->Header->GatewayTxnId;
        $alis->giftCard = new HpsGiftCard($item->CardData);
        $alis->responseCode = (isset($item->RspCode) ? $item->RspCode : null);
        $alis->responseText = (isset($item->RspText) ? $item->RspText : null);

        return $alis;
    }
}