<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_beacl_acl'] = Array (
	'ctrl' => $TCA['tx_beacl_acl']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'type,object_id,permissions,recursive'
	),
	'feInterface' => $TCA['tx_beacl_acl']['feInterface'],
	'columns' => Array (
		'type' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:be_acl/locallang_db.php:tx_beacl_acl.type',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:be_acl/locallang_db.php:tx_beacl_acl.type.I.0', '0'),
					Array('LLL:EXT:be_acl/locallang_db.php:tx_beacl_acl.type.I.1', '1'),
				),
				'size' => 1,
				'maxitems' => 1,
			)
		),
		'object_id' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:be_acl/locallang_db.php:tx_beacl_acl.object_id',
			'config' => Array (
				'type' => 'select',
				'itemsProcFunc' => 'tx_beacl_objsel->select',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'permissions' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:be_acl/locallang_db.php:tx_beacl_acl.permissions',
			'config' => Array (
				'type' => 'check',
				'cols' => 5,
				'items' => Array (
					Array('LLL:EXT:be_acl/locallang_db.php:tx_beacl_acl.permissions.I.0', ''),
					Array('LLL:EXT:be_acl/locallang_db.php:tx_beacl_acl.permissions.I.1', ''),
					Array('LLL:EXT:be_acl/locallang_db.php:tx_beacl_acl.permissions.I.2', ''),
					Array('LLL:EXT:be_acl/locallang_db.php:tx_beacl_acl.permissions.I.3', ''),
					Array('LLL:EXT:be_acl/locallang_db.php:tx_beacl_acl.permissions.I.4', ''),
				),
			)
		),
		'recursive' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:be_acl/locallang_db.php:tx_beacl_acl.recursive',
			'config' => Array (
				'type' => 'check'
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'type;;;;1-1-1, object_id, permissions, recursive'),
		'1' => Array('showitem' => 'type;;;;1-1-1, object_id, permissions, recursive')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);

?>
