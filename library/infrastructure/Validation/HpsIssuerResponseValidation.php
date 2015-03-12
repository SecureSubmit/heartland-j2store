<?php

class HpsIssuerResponseValidation {
    static public $_issuerCodeToCreditExceptionCode = array(
        '02' => HpsExceptionCodes::CardDeclined,
        '03' => HpsExceptionCodes::CardDeclined,
        '04' => HpsExceptionCodes::CardDeclined,
        '05' => HpsExceptionCodes::CardDeclined,
        '41' => HpsExceptionCodes::CardDeclined,
        '43' => HpsExceptionCodes::CardDeclined,
        '44' => HpsExceptionCodes::CardDeclined,
        '51' => HpsExceptionCodes::CardDeclined,
        '56' => HpsExceptionCodes::CardDeclined,
        '61' => HpsExceptionCodes::CardDeclined,
        '62' => HpsExceptionCodes::CardDeclined,
        '63' => HpsExceptionCodes::CardDeclined,
        '65' => HpsExceptionCodes::CardDeclined,
        '78' => HpsExceptionCodes::CardDeclined,
        '06' => HpsExceptionCodes::ProcessingError,
        '07' => HpsExceptionCodes::ProcessingError,
        '12' => HpsExceptionCodes::ProcessingError,
        '15' => HpsExceptionCodes::ProcessingError,
        '19' => HpsExceptionCodes::ProcessingError,
        '52' => HpsExceptionCodes::ProcessingError,
        '53' => HpsExceptionCodes::ProcessingError,
        '57' => HpsExceptionCodes::ProcessingError,
        '58' => HpsExceptionCodes::ProcessingError,
        '76' => HpsExceptionCodes::ProcessingError,
        '77' => HpsExceptionCodes::ProcessingError,
        '96' => HpsExceptionCodes::ProcessingError,
        'EC' => HpsExceptionCodes::ProcessingError,
        '13' => HpsExceptionCodes::InvalidAmount,
        '14' => HpsExceptionCodes::IncorrectNumber,
        '54' => HpsExceptionCodes::ExpiredCard,
        '55' => HpsExceptionCodes::InvalidPin,
        '75' => HpsExceptionCodes::PinEntriesExceeded,
        '80' => HpsExceptionCodes::InvalidExpiry,
        '86' => HpsExceptionCodes::PinVerification,
        '91' => HpsExceptionCodes::IssuerTimeout,
        'EB' => HpsExceptionCodes::IncorrectCvc,
        'N7' => HpsExceptionCodes::IncorrectCvc
    );

    static public $_creditExceptionCodeToMessage = array(
        HpsExceptionCodes::CardDeclined =>
        "The card was declined.",
        HpsExceptionCodes::ProcessingError =>
        "An error occurred while processing the card.",
        HpsExceptionCodes::InvalidAmount =>
        "Must be greater than or equal 0.",
        HpsExceptionCodes::ExpiredCard =>
        "The card has expired.",
        HpsExceptionCodes::InvalidPin =>
        "The 4-digit pin is invalid.",
        HpsExceptionCodes::PinEntriesExceeded =>
        "Maximum number of pin retries exceeded.",
        HpsExceptionCodes::InvalidExpiry =>
        "Card expiration date is invalid.",
        HpsExceptionCodes::PinVerification =>
        "Can't verify card pin number.",
        HpsExceptionCodes::IncorrectCvc =>
        "The card's security code is incorrect.",
        HpsExceptionCodes::IssuerTimeout =>
        "The card issuer timed-out.",
        HpsExceptionCodes::UnknownCreditError =>
        "An unknown issuer error has occurred.",
        HpsExceptionCodes::IncorrectNumber =>
        "The card number is incorrect."
    );


    static public function checkResponse($transactionId,$responseCode,$responseText){
        $e = HpsIssuerResponseValidation::getException($transactionId,$responseCode,$responseText);
        
        if($e != null){
            throw $e;
           return $e;
        }
    }
    
    static public function getException($transactionId,$responseCode,$responseText){
        if($responseCode == '85' ||
                $responseCode == '00'){
            return null;
        }
        
        $code = null;
        foreach ( self::$_issuerCodeToCreditExceptionCode as $key => $value) {
            if($key == $responseCode){
                $code = $value;
                break;
            }
        }

        if($code == null){
            return new HpsCreditException($transactionId,HpsExceptionCodes::UnknownCreditError,
                self::$_creditExceptionCodeToMessage[HpsExceptionCodes::UnknownCreditError],
                $responseCode,$responseText);
        }

        $message = null;
        if(self::$_creditExceptionCodeToMessage[$code] != null){
            $message = self::$_creditExceptionCodeToMessage[$code];
        }else{
            $message = 'Unknown issuer error.';
        }

        return new HpsCreditException($transactionId,$code,$message,$responseCode,$responseText);
    }
} 