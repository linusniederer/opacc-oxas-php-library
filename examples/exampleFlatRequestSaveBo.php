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
    '99099',
    'e',
    '',
    '1',
    '',
    '2',
    '1',
    '',
    'Addr.FirstName,Addr.LastName,Addr.HomePage',
    'Addr.HomePage=@https://example.com'
);

// send oxas request via oxasCache
$result = $oxas->flatRequest( 'Biz', 'SaveBo', $parameters );

?>