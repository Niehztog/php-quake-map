<?php
$rootDir = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR;
require $rootDir . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

try {
    $map = new QuakeMap\QuakeMap();
    //$map->load($rootDir . 'data\trivial.map');
    $map->load($rootDir . 'data\q2dm1_sourcemap.map');

    $map->save('C:\Users\Nils\Desktop\test.map');
} catch (Exception $e) {
    echo $e->getMessage();
}
