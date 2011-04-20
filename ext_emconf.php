<?php

########################################################################
# Extension Manager/Repository config file for ext "be_acl".
#
# Auto generated 20-04-2011 20:08
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Backend ACLs',
	'description' => 'Backend Access Control Lists',
	'category' => 'be',
	'shy' => 0,
	'version' => '1.4.2',
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
			'php' => '3.0.0-0.0.0',
			'typo3' => '3.5.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:17:{s:24:"class.tx_beacl_hooks.php";s:4:"9ccb";s:16:"ext_autoload.php";s:4:"001a";s:21:"ext_conf_template.txt";s:4:"7db6";s:12:"ext_icon.gif";s:4:"1bea";s:17:"ext_localconf.php";s:4:"3d80";s:14:"ext_tables.php";s:4:"aceb";s:14:"ext_tables.sql";s:4:"1076";s:21:"icon_tx_beacl_acl.gif";s:4:"1bea";s:16:"locallang_db.php";s:4:"e78d";s:7:"tca.php";s:4:"01cc";s:14:"doc/manual.sxw";s:4:"4db3";s:19:"doc/wizard_form.dat";s:4:"e68f";s:20:"doc/wizard_form.html";s:4:"4e9b";s:10:"res/acl.js";s:4:"80ec";s:36:"res/class.tx_beacl_userauthgroup.php";s:4:"3d8c";s:38:"res/class.ux_sc_mod_web_perm_index.php";s:4:"089f";s:22:"res/locallang_perm.php";s:4:"4435";}',
	'suggests' => array(
	),
);

?>