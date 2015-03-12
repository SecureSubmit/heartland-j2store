<?php

class HpsReversal extends HpsTransaction{
    public  $avsResultCode  = null,
            $avsResultText  = null,
            $cvvResultCode  = null,
            $cvvResultText  = null,
            $cpcIndicator   = null;

    public static function fromDict($rsp,$txnType,$returnType = 'HpsReversal'){
        $reverseResponse = $rsp->Transaction->$txnType;

        $reverse = parent::fromDict($rsp,$txnType,$returnType);
        $reverse->avsResultCode = (isset($reverseResponse->AVSRsltCode) ? $reverseResponse->AVSRsltCode : null);
        $reverse->avsResultText = (isset($reverseResponse->AVSRsltText) ? $reverseResponse->AVSRsltText : null);
        $reverse->ccpIndicator = (isset($reverseResponse->CPCInd) ? $reverseResponse->CPCInd : null);
        $reverse->cvvResultCode = (isset($reverseResponse->CVVRsltCode) ? $reverseResponse->CVVRsltCode : null);
        $reverse->cvvResultText = (isset($reverseResponse->CVVRsltText) ? $reverseResponse->CVVRsltText : null);

        return $reverse;
    }
} 