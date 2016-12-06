<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('
	options.saveDocNew.tx_beacl_acl=1
');


$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['calcPerms'][] = 'JBartels\\BeAcl\\Utility\\UserAuthGroup->calcPerms';
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['getPagePermsClause'][] = 'JBartels\\BeAcl\\Utility\\UserAuthGroup->getPagePermsClause';

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Beuser\\Controller\\PermissionController'] = array(
	'className' => 'JBartels\\BeAcl\\Controller\\PermissionController',
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'JBartels\\BeAcl\\Hook\\DataHandlerHook';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = 'JBartels\\BeAcl\\Hook\\DataHandlerHook';



$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_be_acl_timestamp'] = array(
	'frontend' => 'TYPO3\\CMS\\Core\\Cache\\Frontend\\StringFrontend',
	'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\SimpleFileBackend',
);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_be_acl_permissions'] = array(
	'frontend' => 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend',
	'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\SimpleFileBackend',
);

if (extension_loaded('redis')) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_be_acl_timestamp']['backend'] = 'TYPO3\\CMS\\Core\\Cache\\Backend\\RedisBackend';
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_be_acl_permissions']['backend'] = 'TYPO3\\CMS\\Core\\Cache\\Backend\\RedisBackend';
}


$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
$iconRegistry->registerIcon(
    'tx_beacl-object-info',
    \TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider::class,
    [
        'name'     => 'info'
    ]
);