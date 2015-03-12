<?php

class HpsGatewayResponseValidation {
    static public function checkResponse($response, $expectedType){
        $rspCode = $response->Header->GatewayRspCode;
        $rspText = $response->Header->GatewayRspMsg;
        $e = HpsGatewayResponseValidation::getException($rspCode, $rspText);

        if($e != null){
            throw $e;
        }

        if(!isset($response->Transaction) || !isset($response->Transaction->$expectedType) ){
            throw new HpsGatewayException(HpsExceptionCodes::UnexpectedGatewayResponse,
                'Unexpected response from HPS gateway.');
        }
    }

    static public function getException($responseCode, $responseText){
        if( $responseCode == '0'){
            return null;
        }
        elseif($responseCode == '-2'){
            return new HpsAuthenticationException(HpsExceptionCodes::AuthenticationError,
                'Authentication Error. Please Double Check your service configuration.');
        }
        elseif($responseCode == '1'){
            return new HpsGatewayException(HpsExceptionCodes::UnknownGatewayError,$responseText,
                $responseCode,$responseText);
        }
        elseif($responseCode == '3'){
            return new HpsGatewayException(HpsExceptionCodes::InvalidOriginalTransaction,$responseText,
                $responseCode,$responseText);
        }
        elseif($responseCode == '5'){
            return new HpsGatewayException(HpsExceptionCodes::NoOpenBatch,$responseText,$responseCode,
                $responseText);
        }
        elseif($responseCode == '12'){
            return new HpsGatewayException(HpsExceptionCodes::InvalidCpcData,'Invalid CPC data.',
                $responseCode,$responseText);
        }
        elseif($responseCode == '13'){
            return new HpsGatewayException(HpsExceptionCodes::InvalidCardData,'Invalid card data.',
                $responseCode,$responseText);
        }
        elseif($responseCode == '14'){
            return new HpsGatewayException(HpsExceptionCodes::InvalidNumber,'The card number is not valid.',
                $responseCode,$responseText);
        }
        elseif($responseCode == '30'){
            return new HpsGatewayException(HpsExceptionCodes::GatewayTimeout,"Gateway timed out.",
                $responseCode,$responseText);
        }
        else{
            return new HpsGatewayException(HpsExceptionCodes::UnknownGatewayError,$responseText,
                $responseCode,$responseText);
        }

    }
} 