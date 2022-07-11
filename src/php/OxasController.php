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

    protected $endpoint;
    protected $action = "http://www.opacc.com/Opacc/ServiceBus/Interface/Ws/Generic/Generic/";

    /**
     * Constructor
     * 
     * @param string    user 
     * @param string    password
     * @param integer   client
     * @param string    endpoint
     */
    public function __construct( $user, $password, $client, $endpoint ) {
        $this->user = $user;
        $this->password = $password;
        $this->client = $client;
        $this->endpoint = $endpoint;
    }
   
    /**
     * [summary]
     * 
     * @param   string  service
     * @param   integer port
     * @param   array   params
     * @param   array   options
     */
    public function sendRequest( $service, $port, $params, $options ) {
        
        return "";
    }

    /**
     * [summary]
     * 
     * @param string password (optional)
     */
    public function encryptPassword( $password = null ) {
        
        if( $password == null ) {
            $soapEncryptPassword = new OxasSoapEncryptPassword( $this->password, $this->endpoint, $this->getSoapClient() );
        } else {
            // use $password
        }

        return "";
    }

    /**
     * [summary]
     * 
     * @param integer   client
     */
    public function setclient( $client ) {
        $this->client = $client;
    }

    /**
     * [summary]
     * 
     * @return SoapClient   soapClient
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