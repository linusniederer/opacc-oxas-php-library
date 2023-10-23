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
     * @param int startAtByte
     * @param int maxBytes
     * @param int mediaFileId (optional)
     */
    public function loadFile( $fileId, $startAtByte = 0, $mediaFileId = null ) {

        if( !empty($fileId) ) {

            // get file information
            $file = $this->getFile( $fileId );

            // prepare requestParams
            $requestParams = array(
                $fileId,
                $startAtByte,
                $file[0]["Return.FileSize"],
                $mediaFileId,
            );

            $data     = $this->flatRequest( 'LoadFile', $requestParams );
            $fileName = $this->convertBase64ToFile($data[0]["Return"], $file[0]["Return.RealFileName"]);

            return array(
                'fileName' => $fileName,
                'fileType' => $file[0]["Return.FileExtension"],
                'filePath' => $this->folder,
                'fileDate' => $file[0]["Return.LastIndexedTS"],
            );
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

        // get document list
        $documents = $this->getDocumentList( $documentCategoryId, $folderId );

        foreach ( $documents as $document ) {

            $documentVersion = $document['Return.CurrentVersionId'];
            $documentId      = $document['Return.DocumentId'];
            
            // get file version for each file in document list
            $fileVersion = $this->getDocumentVersionFileList($documentId, $documentVersion);

            // get file from DMAS
            $fileId  = $fileVersion[0]["Return.FileId"];
            $files[] = $this->loadFile($fileId);
        }

        return $files;
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
     * This method converts a Base64 string to a file and saves it to the folder path
     *
     * @param string base64
     * @param string fileName
     *
     * @return string fileName
     */
    private function convertBase64ToFile($base64, $fileName) {

        // define filePath
        $filePath = $this->folder . '/' . $fileName;

        // remove flags from base64 string
        $base64 = str_replace('[Base64:', '', $base64);
        $base64 = rtrim($base64, ']');

        // Decode the Base64 string
        $bin = base64_decode($base64, true);
        file_put_contents($filePath, $bin);

        return $fileName;
    }
}