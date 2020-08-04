<?php
if (empty($_REQUEST['action']) and empty($_REQUEST['gtsnotify_action'])) {
    $message = 'Access denied action.php';
    echo json_encode(
            ['success' => false,
            'message' => $message,]
            );
    return;
}



define('MODX_API_MODE', true);
require dirname(dirname(dirname(dirname(__FILE__)))) . '/index.php';

$_REQUEST['action'] = $_REQUEST['gtsnotify_action'];

$gtsNotify = $modx->getService('gtsNotify', 'gtsNotify', MODX_CORE_PATH . 'components/gtsnotify/model/', []);
if (!$gtsNotify) {
    $message =  'Could not create gtsnotify!';
	echo json_encode(
		['success' => false,
		'message' => $message,]
		);
	return;
}

$modx->lexicon->load('gtsnotify:default');

$response = $gtsNotify->handleRequest($_REQUEST['action'],$_REQUEST);

echo json_encode($response);