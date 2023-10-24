<?php

// import classes
require '..\src\OxasDmasController.php';

// set oxas connection settings
$user       = '';
$password   = '';
$client     = 10;
$endpoint   = ''; 
$storage    = '';

// create new instances of OxasDmasController
$oxasDmas   = new OxasDmasController( $user, $password, $client, $endpoint, $storage );

// get DMAS folder list
$parentFolderId = '';
$dmasFolders    = $oxasDmas->getFolderList( $parentFolderId );

// load files from DMAS folder
$parentFolderId = '';
$categoryId     = '';
$dmasFiles      = $oxasDmas->loadFilesFromFolder( $parentFolderId, $categoryId );

// load single file from DMAS 
$fileId         = '';
$dmasFile       = $oxasDmas->loadFile( $fileId );

?>