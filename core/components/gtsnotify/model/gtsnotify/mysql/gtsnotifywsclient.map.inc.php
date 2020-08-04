<?php
$xpdo_meta_map['gtsNotifyWSClient']= array (
  'package' => 'gtsnotify',
  'version' => '1.1',
  'table' => 'gtsnotify_ws_clients',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'ws_id' => '',
    'user_id' => NULL,
  ),
  'fieldMeta' => 
  array (
    'ws_id' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '191',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'user_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
    ),
  ),
  'indexes' => 
  array (
    'ws_id' => 
    array (
      'alias' => 'ws_id',
      'primary' => false,
      'unique' => true,
      'type' => 'BTREE',
      'columns' => 
      array (
        'ws_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
);
