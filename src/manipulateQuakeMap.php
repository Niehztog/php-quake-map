<?php
$rootDir = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR;
require $rootDir . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

/* determine home directory */
$homeDirectory = isset($_SERVER['HOME']) ? $_SERVER['HOME'] : $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];

try {
    $map = new QuakeMap\QuakeMap();
    //$map->load($rootDir . 'data' .DIRECTORY_SEPARATOR. 'trivial.map');
    $map->load($rootDir . 'data' .DIRECTORY_SEPARATOR. 'q2dm1_sourcemap.map');

    $map->save($homeDirectory . DIRECTORY_SEPARATOR . 'test.map');
} catch (Exception $e) {
    echo $e->getMessage();
}
