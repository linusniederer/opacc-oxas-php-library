<?php
/**
 * 
 * OxasRequestCache Class
 * 
 * This class creates a cache to store results from requests.
 * When initializing the class, the path for the cache can be set.
 *  
 * @author https://github.com/linusniederer
 * @source https://github.com/linusniederer/opacc-oxas-php-library/blob/main/src/OxasRequestCache.php
 * @version 1.0
 */
class OxasRequestCache {

    protected $folder;
    protected $oxasController;

    /**
     * Constructor
     * 
     * @param string folder
     * @param OxasController oxasController
     */
    public function __construct( $folder, $oxasController ) {
        $this->folder = $folder;
        $this->oxasController = $oxasController;
    }

    /**
     * This method processes the soap requests
     * 
     * @param string port
     * @param string operation
     * @param array requestParams
     * @param array cacheOptions
     * 
     * @return array parsed soap result
     */
    public function flatRequest( $port, $operation, $requestParams, $cacheOptions ) {
 
        $cacheName = $cacheOptions['name'];
        $cacheAge = $cacheOptions['age'];

        if( $this->checkCache( $cacheName, $cacheAge ) ) {

            return $this->getCache( $cacheName );
        
        } else {

            $requestInformations = array(
                'endpoint'  => $this->oxasController->endpoint,
                'user'      => $this->oxasController->user,
                'password'  => $this->oxasController->encryptPassword(),
                'client'    => $this->oxasController->client,
                'port'      => $port, 
                'operation' => $operation,
                'soapClient'=> $this->oxasController->getSoapClient(),
                'requestParams' => $requestParams
            );
    
            $soapFlatRequest = new OxasSoapFlatRequest( $requestInformations );

            $this->setCache( $cacheName, $soapFlatRequest->soapResult );

            return $soapFlatRequest->parseSoapResult();
        }
    }

    /**
     * This class checks if a cache was created for the existing request
     * 
     * @param string cacheName
     * @param integer cacheAge
     * 
     * @return boolean cache exists
     */
    private function checkCache( $cacheName, $cacheAge ) {

        $cacheFile = $this->folder . '' . $cacheName;

        if( file_exists( $cacheFile ) ) {
            
            $filemtime = @filemtime($cacheFile);

            if ( !$filemtime or ( time() - $filemtime <= $cacheAge * 60 ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * This method loads the cache from the directory
     * 
     * @param string cacheName
     * 
     * @return array parsed soap result
     */
    private function getCache( $cacheName ) {

        $cacheFile = $this->folder . '/' . $cacheName;
        $cacheData = new SimpleXMLElement( $cacheFile, 0, TRUE );
        
        return $this->parseCache( $cacheData );
    }

    /**
     * This method creates a new cache file in the directory
     * 
     * @param string cacheName
     * @param simpleXML data
     */
    private function setCache( $cacheName, $data ) {

        $cacheFile = $this->folder . '/' . $cacheName;
        $data->saveXML($cacheFile);
    }

    /**
     * This method parses the read cache and returns it
     * 
     * @param simpleXML cacheData
     * 
     * @return array parsed soap result
     */
    private function parseCache( $cacheData ) {

        $jsonString = json_encode( $cacheData );
        $result_array = json_decode($jsonString, TRUE);

        $rowCount = $result_array['sBody']['FlatRequestResponse']['ResponseData']['RowCount'];
        $columnCount = $result_array['sBody']['FlatRequestResponse']['ResponseData']['ColumnCount'];

        if( $rowCount > 1) {
            return $this->parseCacheMultiRows( $result_array, $rowCount, $columnCount );
        } else {
            return $this->parseCacheSingleRow( $result_array, $columnCount );
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
    private function parseCacheMultiRows( $result_array, $rowCount, $columnCount ) {
        
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
    private function parseCacheSingleRow( $result_array, $columnCount ) {

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
}
