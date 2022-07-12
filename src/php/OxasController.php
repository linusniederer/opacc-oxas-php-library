<?php
/**
 * 
 * [summary]
 * [description]
 * 
 * @author https://github.com/linusniederer
 * @source https://github.com/linusniederer/opacc-oxas/src/php/OxasController.php
 * @version 1.0
 */
class OxasController {

    protected $user;
    protected $password;
    protected $client;

    protected $encryptedPassword;
    protected $endpoint;

    /**
     * Constructor
     * 
     * @param string user 
     * @param string password
     * @param integer client
     * @param string endpoint
     */
    public function __construct( $user, $password, $client, $endpoint ) {

        require dirname(__FILE__) . '\OxasSoapEncryptPassword.php';
        require dirname(__FILE__) . '\OxasSoapFlatRequest.php';

        $this->user     = $user;
        $this->password = $password;
        $this->client   = $client;
        $this->endpoint = $endpoint;
    }
   
    /**
     * [summary]
     * 
     * @param string port
     * @param string operation
     * @param array requestParams
     */
    public function flatRequest( $port, $operation, $requestParams ) {

        $requestInformations = array(
            'endpoint'  => $this->endpoint,
            'user'      => $this->user,
            'password'  => $this->encryptPassword(),
            'client'    => $this->client,
            'port'      => $port, 
            'operation' => $operation,
            'soapClient'=> $this->getSoapClient(),
            'requestParams' => $requestParams
        );

        $soapFlatRequest = new OxasSoapFlatRequest( $requestInformations );
        
        return $soapFlatRequest->parseSoapResult();
    }

    /**
     * [summary]
     * 
     * @param string password (optional)
     * @return string encrypted password
     */
    public function encryptPassword( $password = null ) {
        
        if( $password == null ) {
            $soapEncryptPassword = new OxasSoapEncryptPassword( $this->password, $this->endpoint, $this->getSoapClient() );
        } else {
            $soapEncryptPassword = new OxasSoapEncryptPassword( $password, $this->endpoint, $this->getSoapClient() );
        }

        $this->encryptPassword = $soapEncryptPassword->parseSoapResult();
        return $this->encryptPassword;
    }

    /**
     * [summary]
     * 
     * @return SoapClient soapClient
     */
    private function getSoapClient() {
        $soapClient = new SoapClient( 
            null, array( 
                'location' => $this->endpoint,
                'uri' => 'http://www.opacc.com/Opacc/ServiceBus/Interface/Ws/Generic',
                'trace'=>1, 
                'exceptions'=> 1, 			
                'encoding' => 'UTF-8',
            )
        );

        return $soapClient;
    }

}

?>