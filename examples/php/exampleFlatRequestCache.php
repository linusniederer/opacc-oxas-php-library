<?php

// import classes
require '.\OxasController.php';
require '.\OxasRequestCache.php';

// set oxas connection settings
$user       = '';
$password   = '';
$client     = 10;
$endpoint   = ''; 

// set cache settings
$cacheFolder = 'C:\Temp\cache';

// create new instances of OxasController and OxasRequestCache
$oxas       = new OxasController( $user, $password, $client, $endpoint );
$oxasCache  = new OxasRequestCache( $cacheFolder, $oxas );

// create new oxas request
$parameters = array(
    'Addr',
    '0',
    'n',
    '1',
    '10',
    '',
    '',
    'Addr.Number,Addr.FirstName,Addr.LastName'
);

// set cache options for this request
$cacheOptions = array( 
    'name'  => 'exampleFlatRequest.cache',
    'age'   => 60
);

// send oxas request via oxasCache
$result = $oxasCache->flatRequest( 'Biz', 'GetBo', $parameters, $cacheOptions );

?>