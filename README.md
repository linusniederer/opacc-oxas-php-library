# OpaccOXAS PHP Library
The OpaccOXAS PHP Library provides easy access to OpaccOXAS. The library has four different classes, which must be included in the project. 

## Table of Contents
- [OpaccOXAS PHP Library](#opaccoxas-php-library)
  - [Table of Contents](#table-of-contents)
  - [General Information](#general-information)
  - [Usage (without cache)](#usage-without-cache)
  - [Usage (with cache)](#usage-with-cache)

## General Information
The different classes are described below.

|Classname|Description|
|---|---|
|OxasController|This class manages the connection to OpaccOXAS|
|OxasDmasController|This class manages the connection to OpaccDMAS|
|OxasRequestCache|This class manages and modifies the cache|
|OxasSoapEncryptPassword|This class is used to send a password encryption request|
|OxasSoapFlatRequest|This class is used to send a soap flat request|

There is a script example for each of these classes in th examples folder. These example scripts can be used during development.

## Usage (without cache)
The library can be used with or without cache class. For large requests, it makes sense to store the data in a cache.

![Diagram: OxasRequest without cache](https://github.com/linusniederer/opacc-oxas-php-library/blob/main/doc/OxasRequestNoCache.png?raw=true)

Before the library can be used, the library must be imported. The import path must be adjusted according to the project.

```php
require '.\src\OxasController.php';
```

**Important:** The OxasController imports the classes OxasSoapEncryptPassword and OxasSoapFlatRequest. For this reason, these two classes must be located in the same folder as the OxasController.

Now a new instance of the OxasController must be instantiated:

```php
$user       = '';           // OpaccOXAS User with correct permissions
$password   = '';           // Unencrypted password
$client     = 10;           // OpaccOXAS Client (Mandant)
$endpoint   = '';           // OpaccOXAS Soap Endpoint (not wsdl!)

$oxas = new OxasController( $user, $password, $client, $endpoint );
```

The created $oxas object can now be used to execute methods of the class.

In the following example an encryption of the password is performed:

```php
// Encrypt the password stored in the object
$password = $oxas->encryptPassword();

// Encrypt the password given as parameter
$password = $oxas->encryptPassword('password');
```

Both methods return the encrypted password as a string and additionally store it on the object.

More examples can be found in the examples folder.

## Usage (with cache)
In most cases it makes sense to use a cache for the requests. This way, the data does not have to be reloaded with every site reload.

![Diagram: OxasRequest with cache](https://github.com/linusniederer/opacc-oxas-php-library/blob/main/doc/OxasRequestCache.png?raw=true)

When using the cache, two different libraries must be imported. As can be seen on the diagram, requests are now only sent via the OxasRequestCache.

```php
require '.\src\OxasController.php';
require '.\src\OxasRequestCache.php';
```

**Important:** The OxasController imports the classes OxasSoapEncryptPassword and OxasSoapFlatRequest. For this reason, these two classes must be located in the same folder as the OxasController.

When using the cache, an instance of the OxasController must also be instantiated first. Then the created instance is passed to the OxasRequestCache.

```php
$user       = '';               // OpaccOXAS User with correct permissions
$password   = '';               // Unencrypted password
$client     = 10;               // OpaccOXAS Client (Mandant)
$endpoint   = '';               // OpaccOXAS Soap Endpoint (not wsdl!)

$cacheFolder = '/var/cache/';   // Path to the cache folder with final slash

$oxas       = new OxasController( $user, $password, $client, $endpoint );
$oxasCache  = new OxasRequestCache( $cacheFolder, $oxas );
```

The created $oxasCache object can now be used to execute methods of the class. The same methods can be used as for the OxasController.

The following code requests 1000 addresses from OpaccOXAS. The response should be stored in a cache, which has a lifetime of 30 minutes.

```php
$parameters = array(
    'Addr',
    '0',
    'n',
    '1',
    '1000',
    '',
    '',
    'Addr.Number,Addr.FirstName,Addr.LastName'
);

$cacheOptions = array( 
    'name'  => 'exampleFlatRequest.cache',          // Name of the cache file
    'age'   => 30                                   // Cache lifetime in minutes
);

$result = $oxasCache->flatRequest( 'Biz', 'GetBo', $parameters, $cacheOptions );
```

The method returns a multidimensional array which is stored in the object $result.