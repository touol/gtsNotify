<?php

define('MODX_API_MODE', true);

/** @noinspection PhpIncludeInspection */
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/index.php';

$modx->getService('error', 'error.modError');
$modx->getRequest();
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');
$modx->error->message = null;

if (!$pdoTools = $modx->getService('pdoFetch')) echo "empty pdoTools!";
$modx->addPackage('gtsnotify', MODX_CORE_PATH . 'components/gtsnotify/model/');

$channels0 = array(
	'class' => 'gtsNotifyChannel',
	'where' => [
		'gtsNotifyChannel.email_send'=> 1,
	],
	'select' => [
		'gtsNotifyChannel'=>'id,name, email_tpl, email_sleep'
	],
	'return' => 'data',
	'limit' => 0,
);
$pdoTools->setConfig($channels0, false);
$channels = $pdoTools->run();
foreach($channels as $channel){
	$default = array(
		'class' => 'gtsNotifyChannel',
		'leftJoin' => [
			'gtsNotifyNotifyPurpose'=>[
				'class'=>'gtsNotifyNotifyPurpose',
				'on'=>'gtsNotifyNotifyPurpose.channel_id = gtsNotifyChannel.id',
			],
			'gtsNotifyNotify'=>[
				'class'=>'gtsNotifyNotify',
				'on'=>'gtsNotifyNotify.id = gtsNotifyNotifyPurpose.notify_id',
			],
			'modUser'=>[
				'class'=>'modUser',
				'on'=>'modUser.id = gtsNotifyNotifyPurpose.user_id',
			],
			'modUserProfile'=>[
				'class'=>'modUserProfile',
				'on'=>'modUserProfile.internalKey = gtsNotifyNotifyPurpose.user_id',
			],
		],
		'where' => [
			'gtsNotifyChannel.id'=> $channel['id'],
			'gtsNotifyNotifyPurpose.email_sended'=> 1,
			'gtsNotifyNotify.time:<='=> date('Y-m-d H:i:s',time() - $channel['email_sleep']),
		],
		'select' => [
			'gtsNotifyChannel'=>'gtsNotifyChannel.name, gtsNotifyChannel.email_tpl',
			'gtsNotifyNotify'=>'gtsNotifyNotify.json, gtsNotifyNotify.url, gtsNotifyNotify.time',
			'gtsNotifyNotifyPurpose'=>'gtsNotifyNotifyPurpose.id, gtsNotifyNotifyPurpose.url as purpose_url',
			'modUser'=>$modx->getSelectColumns('modUser','modUser','',array('username')),
			'modUserProfile'=>$modx->getSelectColumns('modUserProfile','modUserProfile','',array(
				'id','internalKey','blocked','blockeduntil','blockedafter','logincount','thislogin','failedlogincount'
				,'sessionid'
				),true),
		],
		'return' => 'data',
		'limit' => 50,
	);
	$pdoTools->setConfig($default, false);
	$purposes = $pdoTools->run();
	if(count($purposes) > 0){
		$mail = $modx->getService('mail', 'mail.modPHPMailer');
		$mail->setHTML(true);
		$purpose_ids = [];
		foreach($purposes as $purpose){
			$purpose_ids[] = $purpose['id'];
		}
		$obj_purposes = $modx->getIterator('gtsNotifyNotifyPurpose',['id:IN'=>$purpose_ids]);
		foreach($obj_purposes as $p){
			$p->email_sended = 2;
			$p->save();
		}
		foreach($purposes as $purpose){
			$body = $pdoTools->getChunk($channel['email_tpl'],$purpose);
			

			$mail->set(modMail::MAIL_SUBJECT, 'Вам отправлено сообщение!');
			$mail->set(modMail::MAIL_BODY, $body);
			$mail->set(modMail::MAIL_SENDER, $modx->getOption('emailsender'));
			$mail->set(modMail::MAIL_FROM, $modx->getOption('emailsender'));
			$mail->set(modMail::MAIL_FROM_NAME, $modx->getOption('site_name'));

			$mail->address('to', $purpose['email']);
			if (!$mail->send()) {
				$this->xpdo->log(xPDO::LOG_LEVEL_ERROR, 'An error occurred while trying to send the email: ' . $mail->mailer->ErrorInfo);
			}
			$mail->reset();
		}
		foreach($obj_purposes as $p){
			$p->email_sended = 3;
			$p->save();
		}
	}	
}
