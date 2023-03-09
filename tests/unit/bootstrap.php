<?php

defined('OXID_SOURCE_PATH') || define('OXID_SOURCE_PATH', '/var/www/html/source');
defined('PLUGIN_SOURCE_PATH') || define('PLUGIN_SOURCE_PATH', '/var/www/html/source/modules/payever');

require_once __DIR__ . DIRECTORY_SEPARATOR .'..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . 'vendor/autoload.php';
$rootOxidAutoload = OXID_SOURCE_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor'
    . DIRECTORY_SEPARATOR . 'autoload.php';
if (file_exists($rootOxidAutoload)) {
    require_once $rootOxidAutoload;
}

require_once OXID_SOURCE_PATH . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once PLUGIN_SOURCE_PATH . DIRECTORY_SEPARATOR . 'autoload.php';
register_shutdown_function(
    function () {
        $error = error_get_last();
        $error && print_r($error);
    }
);
$aModule = [];
include PLUGIN_SOURCE_PATH . DIRECTORY_SEPARATOR . 'metadata.php';
if (!empty($aModule['files']) && is_array($aModule['files'])) {
    foreach ($aModule['files'] as $filePath) {
        $filesToRequire[] = PLUGIN_SOURCE_PATH . DIRECTORY_SEPARATOR
            . str_replace('payever/', '', $filePath);
    }
}
class_alias('Order_List', 'payeverOrderList_parent');
$filesToRequire[] = PLUGIN_SOURCE_PATH . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR
    . 'admin' . DIRECTORY_SEPARATOR . 'payeverorderlist.php';
foreach ($filesToRequire as $fileToRequire) {
    require_once $fileToRequire;
}
$dataBaseInterfaceFilename = OXID_SOURCE_PATH . DIRECTORY_SEPARATOR
    . implode(DIRECTORY_SEPARATOR, ['Core', 'Database', 'Adapter', 'DatabaseInterface']) . '.php';
if (file_exists($dataBaseInterfaceFilename)) {
    require_once $dataBaseInterfaceFilename;
}
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Util' . DIRECTORY_SEPARATOR . 'DatabaseMockTrait.php';
