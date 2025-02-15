<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    $dev = MODX_BASE_PATH . 'Extras/syncRestApi/';
    /** @var xPDOCacheManager $cache */
    $cache = $modx->getCacheManager();
    if (file_exists($dev) && $cache) {
        if (!is_link($dev . 'assets/components/syncrestapi')) {
            $cache->deleteTree(
                $dev . 'assets/components/syncrestapi/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_ASSETS_PATH . 'components/syncrestapi/', $dev . 'assets/components/syncrestapi');
        }
        if (!is_link($dev . 'core/components/syncrestapi')) {
            $cache->deleteTree(
                $dev . 'core/components/syncrestapi/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_CORE_PATH . 'components/syncrestapi/', $dev . 'core/components/syncrestapi');
        }
    }
}

return true;