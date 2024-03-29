<?php
/**
 * 
 * OxasSoapFlatRequest Class
 * 
 * This class sends soap flat requests to OpaccOXAS. 
 * You can choose between different ports and operations. 
 * 
 * @author https://github.com/linusniederer
 * @source https://github.com/linusniederer/opacc-oxas-php-library/blob/main/src/OxasSoapFlatRequest.php
 * @version 1.0
 */
class OxasSoapFlatRequest {

    public $soapResult;
    public $executionTime;

    protected $user;
    protected $encryptedPassword;
    protected $client;

    protected $endpoint;
    protected $operation;
    protected $port;
    
    protected $soapClient;
    protected $requestParams;

    protected $soapAction = "http://www.opacc.com/Opacc/ServiceBus/Interface/Ws/Generic/Generic/FlatRequest";

    /**
     * Constructor
     * 
     * @param array requestInformations
     */
    public function __construct( $requestInformations ) {
        
        $start = microtime(true);

        $this->user              = $requestInformations['user'];
        $this->encryptedPassword = $requestInformations['password'];
        $this->client            = $requestInformations['client'];

        $this->endpoint          = $requestInformations['endpoint'];
        $this->operation         = $requestInformations['operation'];
        $this->port              = $requestInformations['port'];

        $this->soapClient        = $requestInformations['soapClient'];
        $this->requestParams     = $requestInformations['requestParams'];
        
        // do soap request
        $this->sendSoapRequest();
        $this->executionTime = microtime(true) - $start;
    }

    /**
     * This method parses the soap request result
     * 
     * @return array parsed soap result
     */
    public function parseSoapResult() {

        $jsonString = json_encode( $this->soapResult );
        $result_array = json_decode($jsonString, TRUE);

        $rowCount = $result_array['sBody']['FlatRequestResponse']['ResponseData']['RowCount'];
        $columnCount = $result_array['sBody']['FlatRequestResponse']['ResponseData']['ColumnCount'];

        if( $rowCount > 1) {
            return $this->parseSoapResultMultiRows( $result_array, $rowCount, $columnCount );
        } else {
            return $this->parseSoapResultSingleRow( $result_array, $columnCount );
        }        
    }

    /**
     * This method parses responses which have multiple rows
     * 
     * @param array result_array
     * @param integer rowCount
     * @param integer columnCount
     * 
     * @return array parsed soap result
     */
    private function parseSoapResultMultiRows( $result_array, $rowCount, $columnCount ) {
        
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
     * This method parses responses which have only on row
     * 
     * @param array result_array
     * @param integer columnCount
     * 
     * @return array parsed soap result
     */
    private function parseSoapResultSingleRow( $result_array, $columnCount ) {

        $data = array();
        $row = array();

        for( $i = 0; $i < $columnCount; $i++ ) {
            $key = $result_array['sBody']['FlatRequestResponse']['ResponseData']['Columns']['Column'][$i]['Name'];
            $value = $result_array['sBody']['FlatRequestResponse']['ResponseData']['Columns']['Column'][$i]['Rows']['astring'];

            if ( empty( $value ) ) { $value = null; }
            $row[ $key ] = $value;
        }

        array_push($data, $row);
        return $data;
    }

    /**
     * This method sends a soap request to OpaccOXAS
     * 
     * @return SimpleXML soapResult
     */
    private function sendSoapRequest() {

        $soapRequest = $this->getSoapRequest();

        $result = $this->soapClient->__doRequest( $soapRequest, $this->endpoint, $this->soapAction, 1);
        $this->soapResult = simplexml_load_string( preg_replace( "/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $result ) ) or die("ERROR: Can't load XML-Data");

        return $this->soapResult;
    }

    /**
     * This method returns the soap schema as return value
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
     * This method sets the parameters for the request
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