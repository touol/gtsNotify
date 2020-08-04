<?php
$xpdo_meta_map['gtsNotifyNotify']= array (
  'package' => 'gtsnotify',
  'version' => '1.1',
  'table' => 'gtsnotify_notifys',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'json' => '',
    'time' => NULL,
    'url' => '',
  ),
  'fieldMeta' => 
  array (
    'json' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'time' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
      'title' => 'Дата',
    ),
    'url' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '250',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
  ),
  'composites' => 
  array (
    'Purpose' => 
    array (
      'class' => 'gtsNotifyNotifyPurpose',
      'local' => 'id',
      'foreign' => 'notify_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);
