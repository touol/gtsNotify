<?php

return [
    'gtsnotify' => [
        'description' => 'Уведомления',
        'action' => 'home',
        'namespace' => 'gettables',
        'params' => '&config=gtsnotify_notify',
        //&config=test_gts
        //'icon' => '<i class="icon icon-large icon-modx"></i>',
    ],
    'gtsnotify_setting' => [
        'description' => 'Настройки уведомлений',
        'parent'=> 'gtsnotify',
        'action' => 'home',
        'namespace' => 'gettables',
        'params' => '&config=gtsnotify_setting',
        //&config=test_gts
        //'icon' => '<i class="icon icon-large icon-modx"></i>',
    ],
];