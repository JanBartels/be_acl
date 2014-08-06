<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "be_acl".
 *
 * Auto generated 06-08-2014 20:07
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
	'_md5_values_when_last_written' => 'a:19:{s:24:"class.tx_beacl_hooks.php";s:4:"8fae";s:25:"class.tx_beacl_objsel.php";s:4:"059d";s:16:"ext_autoload.php";s:4:"cda2";s:21:"ext_conf_template.txt";s:4:"dc05";s:12:"ext_icon.gif";s:4:"1bea";s:17:"ext_localconf.php";s:4:"9bef";s:14:"ext_tables.php";s:4:"27f0";s:14:"ext_tables.sql";s:4:"15be";s:21:"icon_tx_beacl_acl.gif";s:4:"1bea";s:16:"locallang_db.xml";s:4:"3f3f";s:7:"tca.php";s:4:"f585";s:45:"Classes/Xclass/PermissionModuleController.php";s:4:"8064";s:14:"doc/manual.sxw";s:4:"4db3";s:19:"doc/wizard_form.dat";s:4:"e68f";s:20:"doc/wizard_form.html";s:4:"4e9b";s:10:"res/acl.js";s:4:"8fa6";s:36:"res/class.tx_beacl_userauthgroup.php";s:4:"68a2";s:38:"res/class.ux_sc_mod_web_perm_index.php";s:4:"eea7";s:22:"res/locallang_perm.xml";s:4:"c301";}',
	'suggests' => array(
	),
);

?>