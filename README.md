# OpaccOXAS PHP Library
Using the OpaccOXAS PHP library, requests can be easily sent to OpaccOXAS. The library has four different classes, which must be included in the project. 

The different classes are described below.

|Filename|Classname|Description|
|---|---|---|
|OxasController.php|OxasController|This class manages the connection to OpaccOXAS|
|OxasRequestCache.php|OxasRequestCache|This class manages and modifies the cache|
|OxasSoapEncryptPassword.php|OxasSoapEncryptPassword|This class is used to send a password encryption request|
|OxasSoapFlatRequest.php|OxasSoapFlatRequest|This class is used to send a soap flat request|

There is a script example for each of these classes in th examples folder. These example scripts can be used during development.

## Usage (without Cache)
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
$password   = '';           // Uncrypted password
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

## Usage (with Cache)
In most cases it makes sense to use a cache for the requests. This way, the data does not have to be reloaded with every site reload.

![Diagram: OxasRequest with cache](https://github.com/linusniederer/opacc-oxas-php-library/blob/main/doc/OxasRequestCache.png?raw=true)