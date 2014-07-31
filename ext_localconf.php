<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_beacl_acl=1
');



require_once(t3lib_extMgm::extPath('be_acl').'class.tx_beacl_objsel.php');
require_once(t3lib_extMgm::extPath('be_acl').'res/class.tx_beacl_userauthgroup.php');
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['calcPerms'][] = 'tx_beacl_userAuthGroup->calcPerms';
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['getPagePermsClause'][] = 'tx_beacl_userAuthGroup->getPagePermsClause';


if (version_compare(TYPO3_branch, '6.0', '<')) {
	$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/mod/web/perm/index.php'] = t3lib_extMgm::extPath('be_acl') . 'res/class.ux_sc_mod_web_perm_index.php';
} else {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Perm\\Controller\\PermissionModuleController'] = array(
		'className' => 'Tx_BeAcl_Xclass_PermissionModuleController',
	);
}


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:be_acl/class.tx_beacl_hooks.php:tx_beacl_hooks';

?>
