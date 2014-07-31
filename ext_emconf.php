<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "be_acl".
 *
 * Auto generated 19-06-2014 14:49
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Backend ACLs',
	'description' => 'Backend Access Control Lists',
	'category' => 'be',
	'shy' => 0,
	'version' => '1.5.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Sebastian Kurfuerst',
	'author_email' => 'sebastian@garbage-group.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-5.5.99',
			'typo3' => '4.5.0-6.2.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:19:{s:24:"class.tx_beacl_hooks.php";s:4:"9ccb";s:25:"class.tx_beacl_objsel.php";s:4:"503a";s:16:"ext_autoload.php";s:4:"001a";s:21:"ext_conf_template.txt";s:4:"7db6";s:12:"ext_icon.gif";s:4:"1bea";s:17:"ext_localconf.php";s:4:"2c68";s:14:"ext_tables.php";s:4:"aceb";s:14:"ext_tables.sql";s:4:"1076";s:21:"icon_tx_beacl_acl.gif";s:4:"1bea";s:16:"locallang_db.php";s:4:"e78d";s:7:"tca.php";s:4:"6d44";s:45:"Classes/Xclass/PermissionModuleController.php";s:4:"4f5b";s:14:"doc/manual.sxw";s:4:"4db3";s:19:"doc/wizard_form.dat";s:4:"e68f";s:20:"doc/wizard_form.html";s:4:"4e9b";s:10:"res/acl.js";s:4:"6946";s:36:"res/class.tx_beacl_userauthgroup.php";s:4:"68a2";s:38:"res/class.ux_sc_mod_web_perm_index.php";s:4:"ca90";s:22:"res/locallang_perm.php";s:4:"4435";}',
);

?>