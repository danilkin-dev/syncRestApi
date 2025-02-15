<?php

@ini_set('display_errors', 1);
define('MODX_API_MODE', true);
if (file_exists(dirname(__FILE__, 5).'/index.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(__FILE__, 5).'/index.php';
} elseif (file_exists(dirname(__FILE__, 6).'/index.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(__FILE__, 6).'/index.php';
} else {
    echo "Could not load MODX!\n";
    return;
}

$modx->getService('error','error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);

if (!$syncRestApi = $modx->getService('syncRestApi', '', MODX_CORE_PATH . 'components/syncrestapi/model/syncrestapi/')) {
    return 'Could not load syncRestApi class!';
}

$syncRestApi->initialize();
$syncRestApi->api->authorization();
$syncRestApi->syncProducts();
$syncRestApi->importProducts();