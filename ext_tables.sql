#
# Table structure for table 'tx_beacl_acl'
#
CREATE TABLE tx_beacl_acl (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	type int(11) unsigned DEFAULT '0' NOT NULL,
	object_id int(11) unsigned DEFAULT '0' NOT NULL,
	permissions int(11) unsigned DEFAULT '0' NOT NULL,
	recursive tinyint(1) unsigned DEFAULT '0' NOT NULL,
	PRIMARY KEY (uid),
	UNIQUE KEY uniqueacls (pid,type,object_id,recursive),
	KEY parent (pid)
);
