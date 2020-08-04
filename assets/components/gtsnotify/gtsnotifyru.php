<?php
if (empty($_REQUEST['action'])) {
    $message = 'Access denied action.php';
    echo json_encode(
            ['success' => false,
            'message' => $message,]
            );
    return;
}



define('MODX_API_MODE', true);
require dirname(dirname(dirname(dirname(__FILE__)))) . '/index.php';

$gtsNotifyRu = $modx->getService('gtsNotifyRu', 'gtsNotifyRu', MODX_CORE_PATH . 'components/gtsnotify/providers/gtsnotifyru/', []);
if (!$gtsNotifyRu) {
    $message =  'Could not create gtsNotifyRu!';
	echo json_encode(
		['success' => false,
		'message' => $message,]
		);
	return;
}

$modx->lexicon->load('gtsnotify:default');

$response = $gtsNotifyRu->handleRequest($_REQUEST['action'],$_REQUEST);
//$modx->log(1,"gtsnotifyru.php".print_r($_REQUEST,1));
echo json_encode($response);