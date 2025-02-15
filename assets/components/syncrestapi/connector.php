<?php
if (file_exists(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
} else {
    require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.core.php';
}
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH . 'index.php';
/** @var syncRestApi $syncRestApi */
$syncRestApi = $modx->getService('syncRestApi', 'syncRestApi', MODX_CORE_PATH . 'components/syncrestapi/model/');
$modx->lexicon->load('syncrestapi:default');

// handle request
$corePath = $modx->getOption('syncrestapi_core_path', null, $modx->getOption('core_path') . 'components/syncrestapi/');
$path = $modx->getOption('processorsPath', $syncRestApi->config, $corePath . 'processors/');
$modx->getRequest();

/** @var modConnectorRequest $request */
$request = $modx->request;
$request->handleRequest([
    'processors_path' => $path,
    'location' => '',
]);