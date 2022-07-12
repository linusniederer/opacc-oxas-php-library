<?php
/**
 * 
 * [summary]
 * [description]
 * 
 * @author https://github.com/linusniederer
 * @source https://github.com/linusniederer/opacc-oxas/src/php/OxasSoapEncryptPassword.php
 * @version 1.0
 */
class OxasSoapEncryptPassword {

    protected $password;
    protected $endpoint;
    protected $soapClient;
    protected $soapResult;

    protected $soapAction = "http://www.opacc.com/Opacc/ServiceBus/Interface/Ws/Generic/Generic/EncryptPassword";

    /**
     * Constructor
     * 
     * @param string password
     * @param string endpoint
     */
    public function __construct( $password, $endpoint, $soapClient ) {

        $this->password     = $password;
        $this->endpoint     = $endpoint;
        $this->soapClient   = $soapClient;

        // do soap request
        $test = $this->sendSoapRequest();
    }

    /**
     * [summary]
     * 
     * @return string soapRequest
     */
    public function parseSoapResult() {

        $jsonString = json_encode( $this->soapResult );
        $result_array = json_decode($jsonString, TRUE);

        return $result_array['sBody']['EncryptPasswordResponse']['EncryptPasswordResult'];
    }

    /**
     * [summary]
     * 
     * @return string soapRequest
     */
    private function sendSoapRequest() {

        $soapRequest = $this->getSoapRequest();

        $result = $this->soapClient->__doRequest( $soapRequest, $this->endpoint, $this->soapAction, 1);
        $this->soapResult = simplexml_load_string( preg_replace( "/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $result ) ) or die("ERROR: Can't load XML-Data");

        return $this->soapResult;
    }

    /**
     * [summary]
     * 
     * @return string soapRequest
     */
    private function getSoapRequest() {

        $soapRequest = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:gen="http://www.opacc.com/Opacc/ServiceBus/Interface/Ws/Generic">
            <soapenv:Header/>
            <soapenv:Body>
                <gen:EncryptPassword>
                    <gen:plainPassword>'. $this->password .'</gen:plainPassword>
                </gen:EncryptPassword>
            </soapenv:Body>
        </soapenv:Envelope>';

        return $soapRequest;
    }
}