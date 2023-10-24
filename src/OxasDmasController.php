<?php
/**
 *
 * OxasDmasController Class
 *
 * This class is used to administer the Document Mangament System (DMAS).
 * Through this class an instance of the OxasController is created. All requests are sent to OpaccOXAS by the OxasController.
 *
 * @author https://github.com/linusniederer
 * @source https://github.com/linusniederer/opacc-oxas-php-library/blob/main/src/OxasDmasController.php
 * @version 1.0
 */
class OxasDmasController {

    public $user;
    public $password;
    public $client;
    public $folder;

    public $encryptedPassword;
    public $endpoint;

    public $port = "DMS";
    public $maxJunkFileSizeInByte = 240000;

    /**
     * Constructor
     * 
     * @param string user 
     * @param string password
     * @param integer client
     * @param string endpoint
     * @param string folder
     */
    public function __construct( $user, $password, $client, $endpoint, $folder ) {
        $this->user     = $user;
        $this->password = $password;
        $this->client   = $client;
        $this->endpoint = $endpoint;
        $this->folder   = $folder;
    }

    /**
     * Load a list of subfolders based on parentFolderId
     * 
     * @param string parentFolderId
     * @param string sorteyBy (optional)
     */
    public function getFolderList( $parentFolderId, $sortedBy = null ) {

        if(!empty($parentFolderId)) {

            // DMS.GetFolderList.namedValueStructArray
            $namedValueStructArray = array([
                'Name' => 'ParentFolderId!',
                'Value' => $parentFolderId
            ]);

            // prepare requestParams
            $requestParams = array(
                json_encode($namedValueStructArray),
                $sortedBy,
            );

            return $this->flatRequest( 'GetFolderList', $requestParams );
        }

        return null;
    }

    /**
     * Load a list of documents based on documentCategoryId and folderId
     * 
     * @param string documentCategoryId
     * @param string folderId
     * @param string sorteyBy (optional)
     */
    public function getDocumentList( $documentCategoryId, $folderId, $sortedBy = null ) {

        if(!empty($documentCategoryId) && !empty($folderId)) {

            // DMS.GetFolderList.namedValueStructArray
            $namedValueStructArray = array(
                ['Name' => 'DocumentCategoryId!','Value' => $documentCategoryId],
                ['Name' => 'FolderId!','Value' => $folderId]
            );

            // prepare requestParams
            $requestParams = array(
                json_encode($namedValueStructArray),
                $sortedBy,
            );

            return $this->flatRequest( 'GetDocumentList', $requestParams );
        }

        return null;
    }

    /**
     * Load a version list of a document based on documentId and versionId
     * 
     * @param int documentCategoryId
     * @param int folderId
     * @param string viewerFileFlag (optional)
     * @param string sorteyBy (optional)
     */
    public function getDocumentVersionFileList( $documentId, $versionId, $viewerFileFlag = '1', $sortedBy = null ) {

        if(!empty($documentId) && !empty($versionId) ) {

            // DMS.GetFolderList.namedValueStructArray
            $namedValueStructArray = array([
                'Name' => 'ViewerFileFlag',
                'Value' => $viewerFileFlag
            ]);

            // prepare requestParams
            $requestParams = array(
                $documentId,
                $versionId,
                json_encode($namedValueStructArray),
                $sortedBy,
            );

            return $this->flatRequest( 'GetDocumentVersionFileList', $requestParams );
        }

        return null;
    }

    /**
     * Get a DMAS file based on fileID
     * 
     * @param string fileID
     */
    public function getFile( $fileID ) {

        if( !empty($fileID) ) {

            // prepare requestParams
            $requestParams = array(
                $fileID,
            );

            return $this->flatRequest( 'GetFile', $requestParams );
        }

        return null;
    }

    /**
     * Load a DMAS file based on fileID
     * 
     * @param int fileID
     */
    public function loadFile( $fileId ) {

        if( !empty($fileId) ) {

            // get DMAS File informations
            $file = $this->getFile( $fileId );
            
            // check cache of DMAS files
            $fileFromCache = $this->loadFileFromCache( $file );

            // check if file is newer before loading
            if( empty($fileFromCache) ) {

                $maxJunkFileSizeInByte    = $this->maxJunkFileSizeInByte();
                $fileTransferFromDmsState = 1;

                // prepare variables
                $base64Data     = '';
                $fileInByte     = $file[0]["Return.FileSize"];
                $startAtByte    = 0;

                while ($fileTransferFromDmsState == 1) {

                    // Prepare requestParams
                    $requestParams = array(
                        $fileId,
                        $startAtByte,
                        $maxJunkFileSizeInByte,
                        $mediaFileId,
                    );

                    // Get base64 encoded data from response
                    $response                  = $this->flatRequest('LoadFile', $requestParams);
                    $base64Data               .= $this->filterBase64String($response[0]["Return"]);
                    $fileTransferFromDmsState  = $response[0]["fileTransferFromDmsState"];

                    // Update startAtByte for the next iteration
                    $startAtByte += $maxJunkFileSizeInByte;
                }

                // convert base64 to file
                $fileName = $this->convertBase64ToFile($base64Data, $file[0]["Return.RealFileName"]);

                return array(
                    'fileName' => $fileName,
                    'fileType' => $file[0]["Return.FileExtension"],
                    'filePath' => $this->folder,
                    'fileDate' => $file[0]["Return.LastIndexedTS"],
                    'fileSize' => $fileInByte,
                );
            }

            // return file from cache
            return $fileFromCache;
        }

        return null;
    }

    /**
     * Load all DMAS Files from a specified DMAS Folder by folderId
     * 
     * @param string documentCategoryId
     * @param string folderId
     */
    public function loadFilesFromFolder( $documentCategoryId, $folderId )
    {
        $files = array();
        $category = null;

        // get document list
        $documents = $this->getDocumentList( $documentCategoryId, $folderId );

        foreach ( $documents as $document ) {

            $documentVersion = $document['Return.CurrentVersionId'];
            $documentId      = $document['Return.DocumentId'];
            $category        = $document['Return.FolderName'];
            $title           = $document['Return.Title'];
            
            // get file version for each file in document list
            $fileVersion = $this->getDocumentVersionFileList($documentId, $documentVersion);

            // get file from DMAS
            $fileId  = $fileVersion[0]["Return.FileId"];
            $file    = $this->loadFile($fileId);

            // add attributes
            $file['fileTitle']    = $title;
            $file['fileCategory'] = $category;

            $files[] = $file;
        }

        return $files;
    }

    /**
     * This method processes the soap requests
     * 
     * @param string operation
     * @param array requestParams
     * 
     * @return array parsed soap result
     */
    private function flatRequest( $operation, $requestParams ) {

        $requestInformations = array(
            'endpoint'  => $this->endpoint,
            'user'      => $this->user,
            'password'  => $this->encryptPassword(),
            'client'    => $this->client,
            'port'      => $this->port, 
            'operation' => $operation,
            'soapClient'=> $this->getSoapClient(),
            'requestParams' => $requestParams
        );

        $soapFlatRequest = new OxasSoapFlatRequest( $requestInformations );
        
        return $soapFlatRequest->parseSoapResult();
    }

    /**
     * This method processes the soap requests without request params
     * 
     * @param string operation
     * @param array requestParams
     * 
     * @return array parsed soap result
     */
    private function flatRequestWithoutParams( $operation ) {

        $requestInformations = array(
            'endpoint'  => $this->endpoint,
            'user'      => $this->user,
            'password'  => $this->encryptPassword(),
            'client'    => $this->client,
            'port'      => $this->port, 
            'operation' => $operation,
            'soapClient'=> $this->getSoapClient(),
            'requestParams' => array(),
        );

        return new OxasSoapFlatRequest( $requestInformations );
    }

    /**
     * This method performs an encryption of the password
     * 
     * @param string password (optional)
     * 
     * @return string encrypted password
     */
    private function encryptPassword( $password = null ) {
        
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

    /**
     * This method reads the max junk file size from DMAS
     */
    private function maxJunkFileSizeInByte() {

        if(empty($this->maxJunkFileSizeInByte))
        {
            $maxJunkFileSizeInByte = $this->flatRequestWithoutParams( 'MaxJunkFileSizeInByte' );

            $jsonString   = json_encode( $maxJunkFileSizeInByte );
            $result_array = json_decode($jsonString, true);
            
            return $result_array['soapResult']['sBody']['FlatRequestResponse']['ResponseData']['Columns']['Column']['Rows']['astring'];
        }

        return $this->maxJunkFileSizeInByte;        
    }

    /**
     * This method reads the DMAS file cache 
     * 
     * @param Object file
     * 
     * @return Object|boolean
     */
    private function loadFileFromCache( $file ) {

        // get file path
        $cacheFilePath = $this->folder . '/' . $file[0]["Return.RealFileName"];

        if( file_exists( $cacheFilePath )) {

            $cacheFileTime = @filemtime( $cacheFilePath );
            $fileTime      = $file[0]["Return.LastIndexedTS"];

            if ( !$cacheFileTime or ( strtotime( $cacheFileTime ) < strtotime( $fileTime ) ) ) {
                
                return array(
                    'fileName' => $file[0]["Return.RealFileName"],
                    'fileType' => $file[0]["Return.FileExtension"],
                    'filePath' => $this->folder,
                    'fileDate' => $file[0]["Return.LastIndexedTS"],
                    'fileSize' => $file[0]["Return.FileSize"],
                );
            }
        }

        return null;
    }

    /**
     * This method removes descriptions and brackets from the base64 string
     * 
     * @param string base64
     * 
     * @return string base64
     */
    private function filterBase64String( $base64 ) {

        $base64 = str_replace('[Base64:', '', $base64);
        $base64 = rtrim($base64, ']');

        return $base64;
    }

    /**
     * This method converts a Base64 string to a file and saves it to the folder path
     *
     * @param string base64
     * @param string fileName
     *
     * @return string fileName
     */
    private function convertBase64ToFile( $base64, $fileName ) {

        // define filePath
        $filePath = $this->folder . '/' . $fileName;

        // Decode the Base64 string
        $bin = base64_decode($base64, true);
        file_put_contents($filePath, $bin);

        return $fileName;
    }
}