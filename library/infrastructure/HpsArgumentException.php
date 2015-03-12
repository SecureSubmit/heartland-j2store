<?php

class HpsArgumentException extends HpsException{
    function __construct($message, $code, $innerException = null){
        parent::__construct($message,$code ,$innerException);
    }
} 