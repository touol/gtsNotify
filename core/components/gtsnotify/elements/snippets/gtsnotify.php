<?php
/** @var modX $modx */
/** @var array $scriptProperties */
/** @var gtsNotify $gtsNotify */
$gtsNotify = $modx->getService('gtsNotify', 'gtsNotify', MODX_CORE_PATH . 'components/gtsnotify/model/', $scriptProperties);
if (!$gtsNotify) {
    $modx->log(1,"not gtsNotify");
    return;// 'Could not load gtsNotify class!';
}
$resp = $gtsNotify->new_client();

if(!$resp['success']) {
    $modx->log(1,"not gtsNotify->new_client() ".$resp['message']);
    return;// $resp['message'];
}
$gtsNotify->initialize($modx->context->key,$scriptProperties);
// Do your snippet code here. This demo grabs 5 items from our custom table.
$tpl = $modx->getOption('tpl', $scriptProperties, 'notifys');
$OuterTpl = $modx->getOption('OuterTpl', $scriptProperties, 'outer.notifys');
$outputSeparator = $modx->getOption('outputSeparator', $scriptProperties, "\n");
/*$sortby = $modx->getOption('sortby', $scriptProperties, 'name');
$sortdir = $modx->getOption('sortbir', $scriptProperties, 'ASC');
$limit = $modx->getOption('limit', $scriptProperties, 5);

$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, false);
*/
/** @var pdoFetch $pdoFetch */
$fqn = $modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
$path = $modx->getOption('pdofetch_class_path', null, MODX_CORE_PATH . 'components/pdotools/model/', true);
if ($pdoClass = $modx->loadClass($fqn, $path, false, true)) {
    $pdoFetch = new $pdoClass($modx, $scriptProperties);
} else {
    return false;
}
$pdoFetch->addTime('pdoTools loaded.');

$default = array(
    'class' => 'gtsNotifyChannel',
    'where' => [
        'active'=>1,
        'hidden'=>0,
    ],
    //'leftJoin' => $leftJoin,
    //'innerJoin' => $innerJoin,
    'select' => [
        'gtsNotifyChannel'=>'*'
    ],
    'sortby'=>['sort'=>'ASC'],
    //'groupby' => implode(', ', $groupby),
    'return' => 'data',
);
// Merge all properties and run!
$pdoFetch->setConfig(array_merge($default, $scriptProperties), false);
//echo "<pre>".print_r(array_merge($default, $scriptProperties),1)."</pre>";
$rows = $pdoFetch->run();

// Iterate through items
$list = [];
/** @var gtsNotifyItem $item */
foreach ($rows as &$row) {
    $row['count'] = $modx->getCount('gtsNotifyNotifyPurpose',[
        'active'=>1,
        'channel_id'=>$row['id'],
        'user_id'=> $modx->user->id,
    ]);
    $list[] = $pdoFetch->getChunk($tpl, $row);
}

// Output

if (!empty($toPlaceholder)) {
    // If using a placeholder, output nothing and set output to specified placeholder
    $modx->setPlaceholders($toPlaceholder, $output);

    return '';
}
$output = implode($outputSeparator, $list);
$output = $pdoFetch->getChunk($OuterTpl, ['outer'=>$output]);

// By default just return output
return $output;