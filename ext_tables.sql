CREATE TABLE tt_address (
	oafwm_uid INT(11)                 NOT NULL,
	oafwm_groupname VARCHAR(255) DEFAULT '' NOT NULL,
	oafwm_twitter VARCHAR(255) DEFAULT '' NOT NULL,
	oafwm_orcid VARCHAR(255) DEFAULT '' NOT NULL,
	oafwm_oadays VARCHAR(255) DEFAULT '0' NOT NULL
);


CREATE TABLE oafwm_log (
        uid int(11) unsigned DEFAULT '0' NOT NULL,
        tstamp int(11) unsigned DEFAULT '0' NOT NULL,
        event_pid int(11) DEFAULT '-1' NOT NULL,
        event_userid int(11) NOT NULL default '0',
        tablename varchar(255) DEFAULT '' NOT NULL,
        details text,
        type tinyint(3) unsigned DEFAULT '0' NOT NULL
);

