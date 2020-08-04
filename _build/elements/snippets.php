<?php

return [
    'gtsNotify' => [
        'file' => 'gtsnotify',
        'description' => 'gtsNotify snippet to view notify',
        'properties' => [
            'tpl' => [
                'type' => 'textfield',
                'value' => 'tpl.gtsNotify.notifys',
            ],
            'OuterTpl' => [
                'type' => 'textfield',
                'value' => 'tpl.gtsNotify.outer.notifys',
            ],
            /*'sortby' => [
                'type' => 'textfield',
                'value' => 'name',
            ],
            'sortdir' => [
                'type' => 'list',
                'options' => [
                    ['text' => 'ASC', 'value' => 'ASC'],
                    ['text' => 'DESC', 'value' => 'DESC'],
                ],
                'value' => 'ASC',
            ],
            'limit' => [
                'type' => 'numberfield',
                'value' => 10,
            ],
            'outputSeparator' => [
                'type' => 'textfield',
                'value' => "\n",
            ],
            'toPlaceholder' => [
                'type' => 'combo-boolean',
                'value' => false,
            ],*/
        ],
    ],
];