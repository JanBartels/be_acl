<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

t3lib_extMgm::allowTableOnStandardPages("tx_beacl_acl");

$TCA["tx_beacl_acl"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:be_acl/locallang_db.xml:tx_beacl_acl",
		"label" => "uid",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"type" => "type",
		"default_sortby" => "ORDER BY type",
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_beacl_acl.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "type, object_id, permissions, recursive",
	)
);

?>
