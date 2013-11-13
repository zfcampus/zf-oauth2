<?php
/**
 * Bcrypt utility
 *
 * Generates the bcrypt hash value of a string
 */

$autoload = realpath(__DIR__ . '/../vendor/autoload.php');
$zf2Env   = "ZF2_PATH";

if (file_exists($autoload)) {
    include $autoload;
} elseif (getenv($zf2Env)) {
    include getenv($zf2Env) . '/Zend/Loader/AutoloaderFactory.php';
    Zend\Loader\AutoloaderFactory::factory(array(
        'Zend\Loader\StandardAutoloader' => array(
            'autoregister_zf' => true
        )
    ));
}

if (!class_exists('Zend\Loader\AutoloaderFactory')) {                                                                                
    throw new RuntimeException('Unable to load ZF2. Run `php composer.phar install` or define a ZF2_PATH environment variable.');    
} 

if ($argc < 2) {
    printf("Usage: php bcrypt.php <password> [cost]\n");
    printf("where <password> is the user's password and [cost] is the value\nof the cost parameter of bcrypt (default is 14).\n");
    exit(1);
}

$bcrypt = new Zend\Crypt\Password\Bcrypt;
if (isset($argv[2])) {
    $bcrypt->setCost($argv[2]);
}
printf ("%s\n", $bcrypt->create($argv[1]));

