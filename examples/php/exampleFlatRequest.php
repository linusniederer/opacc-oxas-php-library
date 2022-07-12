<?php

// import class
require '.\OxasController.php';

// set oxas connection settings
$user       = '';
$password   = '';
$client     = 10;
$endpoint   = ''; 

// create new instances of OxasController 
$oxas = new OxasController( $user, $password, $client, $endpoint );

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

// send oxas request via oxasCache
$result = $oxas->flatRequest( 'Biz', 'GetBo', $parameters );

?>