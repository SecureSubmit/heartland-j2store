<?php
class HpsException extends Exception{
    public $innerException = null;
    public $code = null;

    public function __construct($message, $code = null,$innerException = null){
        $this->message = $message;
        $this->code = $code;
        $this->innerException = $innerException;
    }
}
