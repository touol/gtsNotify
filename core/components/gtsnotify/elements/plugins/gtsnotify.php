<?php
/** @var modX $modx */
/* @var array $scriptProperties */
switch ($modx->event->name) {
    case 'OnHandleRequest':
        /* @var gtsNotify $gtsNotify*/
        $gtsNotify = $modx->getService('gtsnotify', 'gtsNotify', $modx->getOption('gtsnotify_core_path', $scriptProperties, $modx->getOption('core_path') . 'components/gtsnotify/') . 'model/');
        if ($gtsNotify instanceof gtsNotify) {
            $gtsNotify->loadHandlerEvent($modx->event, $scriptProperties);
        }
        break;
}
return '';