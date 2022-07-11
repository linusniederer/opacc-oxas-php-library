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
class OxasSoapFlatRequest {

    protected $user;
    protected $encryptedPassword;
    protected $client;

    protected $endpoint;
    protected $operation;
    protected $port;
    
    protected $soapClient;
    protected $requestParams;
    protected $soapResult;

    protected $soapAction = "http://www.opacc.com/Opacc/ServiceBus/Interface/Ws/Generic/Generic/FlatRequest";

    /**
     * Constructor
     * 
     * @param array requestInformations
     */
    public function __construct( $requestInformations ) {

        $this->user              = $requestInformations['user'];
        $this->encryptedPassword = $requestInformations['password'];
        $this->client            = $requestInformations['client'];

        $this->endpoint          = $requestInformations['endpoint'];
        $this->operation         = $requestInformations['operation'];
        $this->port              = $requestInformations['port'];

        $this->soapClient        = $requestInformations['soapClient'];
        $this->requestParams     = $requestInformations['requestParams'];
        
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

        $rowCount = $result_array['sBody']['FlatRequestResponse']['ResponseData']['RowCount'];
        $columnCount = $result_array['sBody']['FlatRequestResponse']['ResponseData']['ColumnCount'];

        $data = array();

        for( $j = 0; $j < $rowCount; $j++ ) {

            $row = array();

            for( $i = 0; $i < $columnCount; $i++ ) {
                $key = $result_array['sBody']['FlatRequestResponse']['ResponseData']['Columns']['Column'][$i]['Name'];
                $value = $result_array['sBody']['FlatRequestResponse']['ResponseData']['Columns']['Column'][$i]['Rows']['astring'][$j];

                if ( empty( $value ) ) { $value = null; }
                $row[ $key ] = $value;
            }

            array_push($data, $row);
        }

        return $data;
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

        $soapRequest = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:gen="http://www.opacc.com/Opacc/ServiceBus/Interface/Ws/Generic" xmlns:ws="http://www.opacc.com/Opacc/ServiceBus/Interface/Ws" xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
            <soapenv:Header/>
            <soapenv:Body>
                <gen:FlatRequest>
                    <gen:PortId>' . $this->port . '</gen:PortId>
                    <gen:OperationId>' . $this->operation . '</gen:OperationId>
                    <gen:RequestContext>
                        <ws:ClientId>' . $this->client . '</ws:ClientId>
                        <ws:UserId>' . $this->user . '</ws:UserId>
                        <ws:Password>' . $this->encryptedPassword . '</ws:Password>
                    </gen:RequestContext>
                    <gen:RequestData>
                        <gen:Parameters>
                            '. $this->getSoapRequestParams() .'
                        </gen:Parameters>
                    </gen:RequestData>
                </gen:FlatRequest>
            </soapenv:Body>
        </soapenv:Envelope>';

        return $soapRequest;
    }

    /**
     * [summary]
     * 
     * @return string parameters
     */
    private function getSoapRequestParams() {

        $parameters = null;

        foreach( $this->requestParams as $key => $value ) {
            $parameters .= ( $value == NULL ) ? '<arr:string></arr:string>' : '<arr:string>' . $value . '</arr:string>';
        }
        
        return $parameters;
    }
    
}