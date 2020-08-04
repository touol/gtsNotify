<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    $dev = MODX_BASE_PATH . 'Extras/gtsNotify/';
    /** @var xPDOCacheManager $cache */
    $cache = $modx->getCacheManager();
    if (file_exists($dev) && $cache) {
        if (!is_link($dev . 'assets/components/gtsnotify')) {
            $cache->deleteTree(
                $dev . 'assets/components/gtsnotify/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_ASSETS_PATH . 'components/gtsnotify/', $dev . 'assets/components/gtsnotify');
        }
        if (!is_link($dev . 'core/components/gtsnotify')) {
            $cache->deleteTree(
                $dev . 'core/components/gtsnotify/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_CORE_PATH . 'components/gtsnotify/', $dev . 'core/components/gtsnotify');
        }
    }
}

return true;