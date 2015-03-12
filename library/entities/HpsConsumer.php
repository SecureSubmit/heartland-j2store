<?php

class HpsConsumer {
    public  $firstName      = null,
            $lastName       = null,
            $phone          = null,
            $email          = null,
            $address        = null;

    function __construct(){
        $this->address = new HpsAddress();
    }
} 