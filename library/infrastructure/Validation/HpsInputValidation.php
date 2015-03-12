<?php

class HpsInputValidation {
    static private $_defaultAllowedCurrencies = array('usd');

    static public function checkAmount($amount){
        if ($amount < 0 || $amount == null){
            throw new HpsInvalidRequestException(HpsExceptionCodes::InvalidAmount,
                'Must be greater than or equal to 0.','amount');
        }
        return sprintf("%0.2f",round($amount,3));
    }

    static public function checkCurrency($currency, $allowedCurrencies = null){
        $currencies = HpsInputValidation::$_defaultAllowedCurrencies;
        if($allowedCurrencies != null){
            $currencies = $allowedCurrencies;
        }

        if ($currency == null or $currency == ""){
            throw new HpsInvalidRequestException(HpsExceptionCodes::MissingCurrency,
                'Currency cannot be none.', 'currency');
        }else if(!in_array(strtolower($currency), $currencies)){
            throw new HpsInvalidRequestException(HpsExceptionCodes::InvalidCurrency,
                'The only supported currency is \'usd\'.', 'currency');
        }
    }

    static public function cleanPhoneNumber($number){
        return preg_replace('/\D+/', '', $number);
    }

    static public function cleanZipCode($zip){
        return preg_replace('/\D+/', '', $zip);
    }

    static public function checkDateNotFuture($date){
        $current = date('Y-m-d\TH:i:s.00\Z',time());

        if($date != null && $date > $current ){
            throw new HpsInvalidRequestException(HpsExceptionCodes::InvalidDate,
                        'Date cannot be in the future.');
        }
    }
} 