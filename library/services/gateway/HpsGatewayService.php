<?php

class HpsGatewayService {
    private $_config = null;
    private $_baseConfig = null;
    private $_url = null;
    protected $_amount = null;
    protected $_currency = null;
    protected $_filterBy = null;

    public function __construct(HpsServicesConfig $config=null){
//        $this->_baseConfig = new HpsConfiguration();
        $this->_config = $config;

//        $this->_url = $this->_baseConfig->soapServiceUri;
//        if($this->_config != null){
//            $this->_url = $this->_config->soapServiceUri;
//        }
//
//        $secretApiKey = $this->_baseConfig->secretApiKey;
//        if($this->_config != null){
//            $secretApiKey = $this->_config->secretApiKey;
//        }


    }

    public function servicesConfig(){
        return $this->_config;
    }

    public function setServicesConfig($value){
        $this->_config = $value;
    }

    public function doTransaction($transaction,$clientTransactionId=null){
    	
        if($this->_isConfigInvalid()){
            throw new HpsAuthenticationException(HpsExceptionCodes::InvalidConfiguration,
                'The HPS SDK has not been properly configured.\n'
                .'Please make sure to initialize the config either\n'
                .'in a service constructor or in your App.config or Web.config file.');
        }

        $xml = new DOMDocument('1.0', 'utf-8');
        $soapEnvelope = $xml->createElement('soapenv:Envelope');
        $soapEnvelope->setAttribute('xmlns:soapenv', 'http://schemas.xmlsoap.org/soap/envelope/');
        $soapEnvelope->setAttribute('xmlns:hps', 'http://Hps.Exchange.PosGateway');

        $soapBody = $xml->createElement('soapenv:Body');
            $hpsRequest = $xml->createElement('hps:PosRequest');
                $hpsVersion = $xml->createElement('hps:Ver1.0');
                    $hpsHeader = $xml->createElement('hps:Header');

                        if ($this->_config->secretApiKey != NULL && $this->_config->secretApiKey != ""){
                            $hpsHeader->appendChild($xml->createElement('hps:SecretAPIKey',$this->_config->secretApiKey));
                        }else{
                            $hpsHeader->appendChild($xml->createElement('hps:SiteId',$this->_config->siteId));
                            $hpsHeader->appendChild($xml->createElement('hps:DeviceId',$this->_config->deviceId));
                            $hpsHeader->appendChild($xml->createElement('hps:LicenseId',$this->_config->licenseId));
                            $hpsHeader->appendChild($xml->createElement('hps:UserName',$this->_config->username));
                            $hpsHeader->appendChild($xml->createElement('hps:Password',$this->_config->password));
                        }
                        if ($this->_config->developerId != null && $this->_config->developerId != ""){
                            $hpsHeader->appendChild($xml->createElement('hps:DeveloperID',$this->_config->developerId));
                            $hpsHeader->appendChild($xml->createElement('hps:VersionNbr',$this->_config->versionNumber));
                            $hpsHeader->appendChild($xml->createElement('hps:SiteTrace',$this->_config->siteTrace));
                        }
                        if ($clientTransactionId != null){
                            $hpsHeader->appendChild($xml->createElement('hps:ClientTxnId', $clientTransactionId));
                        }

                $hpsVersion->appendChild($hpsHeader);
                $transaction = $xml->importNode($transaction,true);
                $hpsVersion->appendChild($transaction);
            $hpsRequest->appendChild($hpsVersion);
        $soapBody->appendChild($hpsRequest);
        $soapEnvelope->appendChild($soapBody);
        $xml->appendChild($soapEnvelope);

        //cURL
        try{
            $header = array(
                "Content-type: text/xml;charset=\"utf-8\"",
                "Accept: text/xml",
                "SOAPAction: \"\"",
                "Content-length: ".strlen($xml->saveXML()),
            );
            $soap_do = curl_init();
            curl_setopt($soap_do, CURLOPT_URL, $this->_gatewayUrlForKey());
            curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($soap_do, CURLOPT_TIMEOUT,        60);
            curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
            curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($soap_do, CURLOPT_POST, true);
            curl_setopt($soap_do, CURLOPT_POSTFIELDS, $xml->saveXML());
            curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);

            if($this->_config->useProxy){
                curl_setopt($soap_do, CURLOPT_PROXY, $this->_config->proxyOptions['proxy_host']);
                curl_setopt($soap_do, CURLOPT_PROXYPORT, $this->_config->proxyOptions['proxy_port']);
            }
            $curlResponse = curl_exec($soap_do);
            $curlInfo = curl_getinfo($soap_do);
            $curlError = curl_errno($soap_do);

            if($curlError == 28){
                throw new HpsException("gateway_time-out");
            }
            if($curlInfo['http_code'] == '200'){
                $responseObject = $this->_XML2Array($curlResponse);
                
                $ver = "Ver1.0";
                return $responseObject->$ver;
            }else{
            	
                throw new HpsException('Unexpected response');
            }
        }catch (Exception $e){
            throw new HpsGatewayException(HpsExceptionCodes::UnknownGatewayError,'Unable to process transaction', null, null, $e);
        }
    }

    private function _gatewayUrlForKey(){
        if($this->_config->secretApiKey != null && $this->_config->secretApiKey != ''){
            if( strpos($this->_config->secretApiKey, '_cert_') !== false){
                
            	return "https://posgateway.cert.secureexchange.net/Hps.Exchange.PosGateway/PosGatewayService.asmx";
            }else{
                return "https://api2.heartlandportico.com/Hps.Exchange.PosGateway/PosGatewayService.asmx";
            }
        }else{
            return $this->_config->soapServiceUri;
        }
    }

    private function _isConfigInvalid(){
        if($this->_config == null && (
                $this->_config->secretApiKey == null ||
                $this->_config->username == null ||
                $this->_config->password == null ||
                $this->_config->licenseId == -1 ||
                $this->_config->deviceId == -1 ||
                $this->_config->siteId == -1)
        ){
            return true;
        }
        return false;
    }

     function _XML2Array($xml){
        $envelope = simplexml_load_string($xml, "SimpleXMLElement", 0,'http://schemas.xmlsoap.org/soap/envelope/');
        foreach($envelope->Body as $response) {
            foreach ($response->children('http://Hps.Exchange.PosGateway') as $item) {
                return $item;
            }
        }
    }
} 
