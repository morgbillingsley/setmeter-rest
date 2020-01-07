<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$paths = array(__DIR__ . "/Models");
$isDevMode = true;
$proxyDir = null;
$cache = null;
$useSimpleAnnotationReader = false;

// the connection configuration
$dbParams = array(
    'driver'   => 'pdo_mysql',
    'user'     => '',
    'password' => '',
    'dbname'   => '',
);

$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader);
$entityManager = EntityManager::create($dbParams, $config);

function autoloader($namespace): bool
{
    $class = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . '.php';
    if (file_exists($class)) {
        require $class;
        return true;
    }
    return false;
}

spl_autoload_register('autoloader');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/App.php';
require_once __DIR__ . '/core/Controller.php';

?>