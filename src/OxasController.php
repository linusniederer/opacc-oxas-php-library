<?php
/**
 * 
 * OxasController Class
 * 
 * This class is used to manage the connection to OpaccOXAS.
 * Requests to OpaccOXAS are sent to this class. The class forwards them to the two request classes.
 * 
 * @author https://github.com/linusniederer
 * @source https://github.com/linusniederer/opacc-oxas-php-library/blob/main/src/OxasController.php
 * @version 1.0
 */
class OxasController {

    public $user;
    public $password;
    public $client;

    public $encryptedPassword;
    public $endpoint;

    /**
     * Constructor
     * 
     * @param string user 
     * @param string password
     * @param integer client
     * @param string endpoint
     */
    public function __construct( $user, $password, $client, $endpoint ) {

        require dirname(__FILE__) . '/OxasSoapEncryptPassword.php';
        require dirname(__FILE__) . '/OxasSoapFlatRequest.php';

        $this->user     = $user;
        $this->password = $password;
        $this->client   = $client;
        $this->endpoint = $endpoint;
    }
   
    /**
     * This method processes the soap requests
     * 
     * @param string port
     * @param string operation
     * @param array requestParams
     * 
     * @return array parsed soap result
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
     * This method performs an encryption of the password
     * 
     * @param string password (optional)
     * 
     * @return string encrypted password
     */
    public function encryptPassword( $password = null ) {
        
        if( $password == null ) {
            $soapEncryptPassword = new OxasSoapEncryptPassword( $this->password, $this->endpoint, $this->getSoapClient() );
        } else {
            $soapEncryptPassword = new OxasSoapEncryptPassword( $password, $this->endpoint, $this->getSoapClient() );
        }

        $this->encryptedPassword = $soapEncryptPassword->parseSoapResult();
        return $this->encryptedPassword;
    }

    /**
     * This method creates a SoapClient which is passed as a parameter to the request classes
     * 
     * @return SoapClient soapClient
     */
    public function getSoapClient() {
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
