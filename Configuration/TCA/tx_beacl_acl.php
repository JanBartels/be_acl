<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

return [
    'ctrl' => [
        'title' => 'LLL:EXT:be_acl/Resources/Private/Languages/locallang_db.xlf:tx_beacl_acl',
        'label' => 'uid',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'type' => 'type',
        'default_sortby' => 'ORDER BY type',
        'iconfile' => 'EXT:be_acl/Resources/Public/Icons/icon_tx_beacl_acl.gif',
    ],
    'feInterface' => [
        'fe_admin_fieldList' => 'type, object_id, permissions, recursive',
    ],
    'interface' => [
        'showRecordFieldList' => 'type,object_id,permissions,recursive',
    ],
    'columns' => [
        'type' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:be_acl/Resources/Private/Languages/locallang_db.xlf:tx_beacl_acl.type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:be_acl/Resources/Private/Languages/locallang_db.xlf:tx_beacl_acl.type.I.0', '0'],
                    ['LLL:EXT:be_acl/Resources/Private/Languages/locallang_db.xlf:tx_beacl_acl.type.I.1', '1'],
                ],
                'size' => 1,
                'maxitems' => 1,
            ],
        ],
        'object_id' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:be_acl/Resources/Private/Languages/locallang_db.xlf:tx_beacl_acl.object_id',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => 'JBartels\BeAcl\Utility\ObjectSelection->select',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'permissions' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:be_acl/Resources/Private/Languages/locallang_db.xlf:tx_beacl_acl.permissions',
            'config' => [
                'type' => 'check',
                'cols' => 5,
                'items' => [
                    ['LLL:EXT:be_acl/Resources/Private/Languages/locallang_db.xlf:tx_beacl_acl.permissions.I.0', ''],
                    ['LLL:EXT:be_acl/Resources/Private/Languages/locallang_db.xlf:tx_beacl_acl.permissions.I.1', ''],
                    ['LLL:EXT:be_acl/Resources/Private/Languages/locallang_db.xlf:tx_beacl_acl.permissions.I.2', ''],
                    ['LLL:EXT:be_acl/Resources/Private/Languages/locallang_db.xlf:tx_beacl_acl.permissions.I.3', ''],
                    ['LLL:EXT:be_acl/Resources/Private/Languages/locallang_db.xlf:tx_beacl_acl.permissions.I.4', ''],
                ],
            ],
        ],
        'recursive' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:be_acl/Resources/Private/Languages/locallang_db.xlf:tx_beacl_acl.recursive',
            'config' => [
                'type' => 'check',
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'type, object_id, permissions, recursive'],
        '1' => ['showitem' => 'type, object_id, permissions, recursive'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
];
