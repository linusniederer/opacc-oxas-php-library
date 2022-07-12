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

// encrypt current password stored in OxasController
$password = $oxas->encryptPassword();

// encrypt password as parameter
$password = $oxas->encryptPassword('examplePassword');

?>