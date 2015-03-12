<?php

class HpsCheckResponse extends HpsTransaction{
    public  $authorizationCode  = null,
            $customerId         = null,
            $details            = null;

    public static function fromDict($rsp,$txnType,$returnType = 'HpsCheckResponse'){
        $response = $rsp->Transaction->$txnType;

        $sale = parent::fromDict($rsp,$txnType,$returnType);
        $sale->responseCode = (isset($response->RspCode) ? $response->RspCode : null);
        $sale->responseText = (isset($response->RspMessage) ? $response->RspMessage : null);
        $sale->authorizationCode = (isset($response->AuthCode) ? $response->AuthCode : null);

        if($response->CheckRspInfo){
            $sale->details = array();

            if(count($response->CheckRspInfo)>1){
                foreach ($response->CheckRspInfo as $key=>$details) {
                    $sale->details[] = self::_hydrateRspDetails($details);
                }
            }else{
                $sale->details = self::_hydrateRspDetails($response->CheckRspInfo);
            }
        }

        return $sale;
    }

    private static function _hydrateRspDetails($checkInfo){
        $details = new HpsCheckResponseDetails();
        $details->messageType = (isset($checkInfo->Type) ? $checkInfo->Type : null);
        $details->code = (isset($checkInfo->Code) ? $checkInfo->Code : null);
        $details->message = (isset($checkInfo->Message) ? $checkInfo->Message : null);
        $details->fieldNumber = (isset($checkInfo->FieldNumber) ? $checkInfo->FieldNumber : null);
        $details->fieldName = (isset($checkInfo->FieldName) ? $checkInfo->FieldName : null);
        return $details;
    }
} 